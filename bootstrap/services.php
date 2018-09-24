<?php

$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('response', \Symfony\Component\HttpFoundation\JsonResponse::class);
$container->register('request', \Symfony\Component\HttpFoundation\Request::createFromGlobals());
$container->register('userService', \Api\Services\UserService::class);
$container->register('restaurantService', \Api\Services\RestaurantService::class);

/** @var \Symfony\Component\HttpFoundation\Request $requestGlobals */
$requestGlobals = $container->get('request');

$container
    ->register(\Api\Controllers\RestaurantController::class, \Api\Controllers\RestaurantController::class)
    ->addArgument(new \Symfony\Component\DependencyInjection\Reference('userService'))
    ->addArgument(new \Symfony\Component\DependencyInjection\Reference('restaurantService'))
    ->addMethodCall('setRequest', [$requestGlobals::createFromGlobals()])
    ->addMethodCall('setResponse', [new \Symfony\Component\DependencyInjection\Reference('response')]);

$container
    ->register(\Api\Controllers\UserController::class, \Api\Controllers\UserController::class)
    ->addArgument(new \Symfony\Component\DependencyInjection\Reference('userService'))
    ->addMethodCall('setRequest', [$requestGlobals::createFromGlobals()])
    ->addMethodCall('setResponse', [new \Symfony\Component\DependencyInjection\Reference('response')]);

return $container;
