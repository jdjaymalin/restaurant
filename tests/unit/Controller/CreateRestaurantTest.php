<?php namespace Tests\Unit\Controller;

use Api\Exceptions\UserAuthorizationFailedException;
use Api\Exceptions\UserNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class CreateRestaurantTest extends AbstractRestaurantController
{
    /**
     * @covers \Api\Controllers\RestaurantController::create()
     */
    public function testCreateRestaurant() : void
    {
        $request = Request::create('/restaurants', 'POST', $this->sampleRestaurantAttributes());
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->willReturn($this->sampleUser());

        $this->restaurantService
            ->expects($this->once())
            ->method('createRestaurant')
            ->with($this->sampleRestaurantAttributes(), $this->sampleUser())
            ->willReturn($this->sampleRestaurant());

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->create();
        $responseBody = json_decode($response->getContent());

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals(json_encode($responseBody->result), json_encode($this->sampleRestaurant()));
    }

    /**
     * @covers \Api\Controllers\RestaurantController::create()
     */
    public function testCreateRestaurantInvalidNameNull() : void
    {
        $request = Request::create('/restaurants', 'POST', $this->sampleRestaurantAttributes(['name' => null]));
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->never())
            ->method('getUserFromToken');

        $this->restaurantService
            ->expects($this->never())
            ->method('createRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->create();
        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::create()
     */
    public function testCreateRestaurantInvalidHasVegetarian() : void
    {
        $request = Request::create(
            '/restaurants',
            'POST',
            $this->sampleRestaurantAttributes(['hasVegetarian' => 'invalid value'])
        );
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->never())
            ->method('getUserFromToken');

        $this->restaurantService
            ->expects($this->never())
            ->method('createRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->create();
        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::create()
     */
    public function testCreateRestaurantInvalidParameters() : void
    {
        $request = Request::create(
            '/restaurants',
            'POST',
            []
        );
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->never())
            ->method('getUserFromToken');

        $this->restaurantService
            ->expects($this->never())
            ->method('createRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->create();
        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::create()
     */
    public function testCreateRestaurantNoToken() : void
    {
        $request = Request::create(
            '/restaurants',
            'POST',
            $this->sampleRestaurantAttributes()
        );

        $this->userService
            ->expects($this->never())
            ->method('getUserFromToken');

        $this->restaurantService
            ->expects($this->never())
            ->method('createRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->create();
        $responseBody = json_decode($response->getContent());

        static::assertEquals(401, $response->getStatusCode());
        static::assertEquals($responseBody->message, 'User authorization failed');
    }

    /**
     * @covers \Api\Controllers\RestaurantController::create()
     */
    public function testCreateRestaurantInvalidUser() : void
    {
        $request = Request::create(
            '/restaurants',
            'POST',
            $this->sampleRestaurantAttributes()
        );
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->with('jwttoken')
            ->willThrowException(new UserNotFoundException);

        $this->restaurantService
            ->expects($this->never())
            ->method('createRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->create();
        $responseBody = json_decode($response->getContent());

        static::assertEquals(401, $response->getStatusCode());
        static::assertEquals($responseBody->message, 'User not found');
    }

    /**
     * @covers \Api\Controllers\RestaurantController::create()
     */
    public function testCreateRestaurantInvalidToken() : void
    {
        $request = Request::create(
            '/restaurants',
            'POST',
            $this->sampleRestaurantAttributes()
        );
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->with('jwttoken')
            ->willThrowException(new UserAuthorizationFailedException());

        $this->restaurantService
            ->expects($this->never())
            ->method('createRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->create();
        $responseBody = json_decode($response->getContent());

        static::assertEquals(401, $response->getStatusCode());
        static::assertEquals($responseBody->message, 'User authorization failed');
    }
}
