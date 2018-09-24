<?php

return [
    ['GET', '/restaurants', [\Api\Controllers\RestaurantController::class, 'all']],
    ['GET', '/restaurants/search', [\Api\Controllers\RestaurantController::class, 'search']],
    ['GET', '/restaurants/{id}', [\Api\Controllers\RestaurantController::class, 'restaurant']],
    ['PATCH', '/restaurants/{id}', [\Api\Controllers\RestaurantController::class, 'update']],
    ['DELETE', '/restaurants/{id}', [\Api\Controllers\RestaurantController::class, 'delete']],
    ['POST', '/auth', [\Api\Controllers\UserController::class, 'auth']],
    ['POST', '/restaurants', [\Api\Controllers\RestaurantController::class, 'create']],
    ['POST', '/user', [\Api\Controllers\UserController::class, 'create']],
];
