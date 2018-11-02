<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use function DI\create;
use function DI\get;
use ExampleApp\HelloWorldRoute;
use function FastRoute\simpleDispatcher;
use Middlewares\FastRoute;
use Middlewares\RequestHandler;
use Relay\Relay;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// init container
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(false);
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions([
    HelloWorldRoute::class => create(HelloWorldRoute::class)->constructor(get('Api'), get('Response')),
    'Api' => 'bar',
    'Response' => function () {
        return new Response();
    }
]);

$container = $containerBuilder->build();

$middlewareQueue = [];

$routes = simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->get('/hello/{message}', HelloWorldRoute::class);
});
// FastRoute determines if a request is valid and can actually be handled by the application
$middlewareQueue[] = new FastRoute($routes);
// RequestHandler sends Request to the handler configured for that route in the routes definition.
$middlewareQueue[] = new RequestHandler($container);

$requestHandler = new Relay($middlewareQueue);
$response = $requestHandler->handle(ServerRequestFactory::fromGlobals());

$emitter = new SapiEmitter();
return $emitter->emit($response);
