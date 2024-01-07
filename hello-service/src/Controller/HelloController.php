<?php

namespace App\Controller;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final readonly class HelloController
{
    public function __construct(
        private MeterInterface $meter,
        private LoggerInterface $logger,
    )
    {
    }

    public function hello(Response $response): Response
    {
        $this->logger->alert('Someone said hello!');

        $val = rand(0, 256);

        $this->meter
            ->createObservableGauge('counter_example_a')
            ->observe(function (ObserverInterface $observer) use ($val) {
                $observer->observe($val);
            });

        $response->getBody()->write(sprintf('Val: %d', $val));
        return $response;
    }
}