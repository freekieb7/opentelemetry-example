<?php

/** @noinspection PhpUnused */
declare(strict_types=1);

use DI\ContainerBuilder;
use Http\Discovery\Psr18ClientDiscovery;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Map\Psr3;
use OpenTelemetry\API\Metrics\MeterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        CachedInstrumentation::class => function (): CachedInstrumentation {
            return new CachedInstrumentation('demo');
        },
        MeterInterface::class => function (ContainerInterface $container): MeterInterface {
            /** @var CachedInstrumentation $instrumentation */
            $instrumentation = $container->get(CachedInstrumentation::class);
            return $instrumentation->meter();
        },
        ClientInterface::class => function (): ClientInterface {
            return Psr18ClientDiscovery::find();
        },
        LoggerInterface::class => static function (ContainerInterface $container): LoggerInterface {
            /** @var CachedInstrumentation $instrumentation */
            $instrumentation = $container->get(CachedInstrumentation::class);

            $otelHandler = new class($instrumentation->logger(), LogLevel::INFO) extends AbstractProcessingHandler {
                public function __construct(
                    private readonly \OpenTelemetry\API\Logs\LoggerInterface $logger,
                    string                           $level,
                    bool                             $bubble = true,
                )
                {
                    parent::__construct($level, $bubble);
                }

                #[\Override] protected function write(\Monolog\LogRecord $record): void
                {
                    $this->logger->emit($this->convert($record->toArray()));
                }

                private function convert(array $record): LogRecord
                {
                    return (new LogRecord($record['message']))
                        ->setSeverityText($record['level_name'])
                        ->setTimestamp((int)(microtime(true) * LogRecord::NANOS_PER_SECOND))
                        ->setObservedTimestamp($record['datetime']->format('U') * LogRecord::NANOS_PER_SECOND)
                        ->setSeverityNumber(Psr3::severityNumber($record['level_name']))
                        ->setAttributes($record['context'] + $record['extra']);
                }
            };

            return new Logger('otel-php-monolog', [$otelHandler]);
        },
    ]);
};
