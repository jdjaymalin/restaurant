<?php

use FastRoute\Dispatcher;

$container = include 'services.php';

/** @var Symfony\Component\HttpFoundation\Request $httpRequest */
$httpRequest = $container->get('request');
$httpRequest = $httpRequest::createFromGlobals();

/** @var Symfony\Component\HttpFoundation\JsonResponse $httpResponse */
$httpResponse = $container->get('response');

$whoops = new \Whoops\Run();
if (getenv('MODE') === 'dev') {
    $whoops->pushHandler(
        new \Whoops\Handler\PrettyPageHandler()
    );
} else {
    $whoops->pushHandler(
        function () use ($httpRequest, $httpResponse) {
            $httpResponse::create('An internal server error has occurred.', $httpResponse::HTTP_INTERNAL_SERVER_ERROR)
                ->prepare($httpRequest)
                ->send();
        }
    );
}
$whoops->register();

$routeDefinitionCallback = function (FastRoute\RouteCollector $r) {
    $routes = include __DIR__ . '/../src/api/routes.php';
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
};

$dispatcher = FastRoute\simpleDispatcher($routeDefinitionCallback);
$routeInfo = $dispatcher->dispatch($httpRequest->getMethod(), $httpRequest->getPathInfo());

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        $httpResponse::create(['error' => '404 Not Found'], $httpResponse::HTTP_NOT_FOUND)
            ->prepare($httpRequest)
            ->send();
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        $httpResponse::create(['error' => '405 Method Not Allowed'], $httpResponse::HTTP_METHOD_NOT_ALLOWED)
            ->prepare($httpRequest)
            ->send();
        break;
    case Dispatcher::FOUND:
        $class = $routeInfo[1][0];
        $routeMethod = $routeInfo[1][1];
        $routeParams = $routeInfo[2];
        $controller = $container->get($class);

        $response = $controller->$routeMethod($routeParams);
        if ($response instanceof $httpResponse) {
            $response
                ->prepare($httpRequest)
                ->send();
        }
        break;
    default:
        $httpResponse::create(['error' => 'Unable to complete request'], $httpResponse::HTTP_BAD_REQUEST)
            ->prepare($httpRequest)
            ->send();
        break;
}
