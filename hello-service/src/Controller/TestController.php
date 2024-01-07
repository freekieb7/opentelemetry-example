<?php

namespace App\Controller;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\RequestFactory;

final readonly class TestController
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactory $requestFactory,
    ) {
    }

    public function test(Response $response): Response
    {
        $request = $this->requestFactory->createRequest('GET', 'http://hello-service-b:8080');
        $this->client->sendRequest($request);

        return $response;
    }
}