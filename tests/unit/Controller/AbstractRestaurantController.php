<?php namespace Tests\unit\Controller;

use Api\Controllers\RestaurantController;
use Api\Models\Restaurant;
use Api\Models\User;
use Api\Services\RestaurantService;
use Api\Services\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractRestaurantController extends TestCase
{
    /** @var MockObject|UserService */
    protected $userService;

    /** @var MockObject|RestaurantService */
    protected $restaurantService;

    /** @var RestaurantController */
    protected $restaurantController;

    public function setUp()
    {
        $this->userService = $this->createMock(UserService::class);
        $this->restaurantService = $this->createMock(RestaurantService::class);
        $this->restaurantController = new RestaurantController($this->userService, $this->restaurantService);

        $jsonResponse = JsonResponse::create();
        $this->restaurantController->setResponse($jsonResponse);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function sampleRestaurantAttributes(array $attributes = []) : array
    {
        return array_merge([
            'name' => 'Pork Sinigang',
            'hasVegetarian' => 'false'
        ], $attributes);
    }

    /**
     * @return Restaurant
     */
    protected function sampleRestaurant() : Restaurant
    {
        return new Restaurant([
            'name' => 'Ishoumarou',
            'hasVegetarian' => false
        ]);
    }

    /**
     * @return User
     */
    protected function sampleUser() : User
    {
        return new User([
            'username' => 'test_user',
            'firstName' => 'Test User',
            'lastName' => 'Test',
        ]);
    }
}
