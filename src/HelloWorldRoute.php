<?php
declare(strict_types=1);

namespace ExampleApp;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HelloWorldRoute
{
    private $foo;

    private $response;

    public function __construct(string $api, ResponseInterface $response)
    {
        $this->foo = $api;
        $this->response = $response;
    }

    public function __invoke(RequestInterface $request): ResponseInterface
    {
        $response = $this->response->withHeader('Content-Type', 'text/html');
        $response->getBody()->write("<html><head></head><body>Hello, {$this->foo} world!</body></html>");

        return $response;
    }

}