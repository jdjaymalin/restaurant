<?php namespace Tests\Unit\Controller;

use Api\Exceptions\RestaurantNotFoundException;
use Api\Exceptions\UserAuthorizationFailedException;
use Api\Exceptions\UserNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class UpdateRestaurantTest extends AbstractRestaurantController
{
    /**
     * @covers \Api\Controllers\RestaurantController::update()
     */
    public function testUpdateRestaurant() : void
    {
        $request = Request::create('/restaurants/1', 'POST', $this->sampleRestaurantAttributes());
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->willReturn($this->sampleUser());

        $this->restaurantService
            ->expects($this->once())
            ->method('getRestaurant')
            ->with(1)
            ->willReturn($this->sampleRestaurant());

        $this->restaurantService
            ->expects($this->once())
            ->method('userCanEditRestaurant')
            ->with($this->sampleUser(), $this->sampleRestaurant())
            ->willReturn(true);

        $this->restaurantService
            ->expects($this->once())
            ->method('updateRestaurant')
            ->with($this->sampleRestaurant(), $this->sampleRestaurantAttributes())
            ->willReturn($this->sampleRestaurant());

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->update(1);

        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::update()
     */
    public function testUpdateRestaurantNoAuthorizationToken() : void
    {
        $request = Request::create('/restaurants/1', 'POST', $this->sampleRestaurantAttributes());

        $this->userService
            ->expects($this->never())
            ->method('getUserFromToken');

        $this->restaurantService
            ->expects($this->never())
            ->method('getRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('userCanEditRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('updateRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->update(1);

        static::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::update()
     */
    public function testUpdateRestaurantInvalidToken() : void
    {
        $request = Request::create('/restaurants/1', 'POST', $this->sampleRestaurantAttributes());
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->willThrowException(new UserAuthorizationFailedException);

        $this->restaurantService
            ->expects($this->never())
            ->method('getRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('userCanEditRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('updateRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->update(1);

        static::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::update()
     */
    public function testUpdateRestaurantUserNotFound() : void
    {
        $request = Request::create('/restaurants/1', 'POST', $this->sampleRestaurantAttributes());
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->willThrowException(new UserNotFoundException);

        $this->restaurantService
            ->expects($this->never())
            ->method('getRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('userCanEditRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('updateRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->update(1);

        static::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::update()
     */
    public function testUpdateRestaurantWhenRestaurantNotFound() : void
    {
        $request = Request::create('/restaurants/1', 'POST', $this->sampleRestaurantAttributes());
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->willReturn($this->sampleUser());

        $this->restaurantService
            ->expects($this->once())
            ->method('getRestaurant')
            ->with(1)
            ->willThrowException(new RestaurantNotFoundException);

        $this->restaurantService
            ->expects($this->never())
            ->method('userCanEditRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('updateRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->update(1);

        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::update()
     */
    public function testUpdateRestaurantWhenUserDoesNotHaveAccess() : void
    {
        $request = Request::create('/restaurants/1', 'POST', $this->sampleRestaurantAttributes());
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->willReturn($this->sampleUser());

        $this->restaurantService
            ->expects($this->once())
            ->method('getRestaurant')
            ->with(1)
            ->willReturn($this->sampleRestaurant());

        $this->restaurantService
            ->expects($this->once())
            ->method('userCanEditRestaurant')
            ->with($this->sampleUser(), $this->sampleRestaurant())
            ->willReturn(false);

        $this->restaurantService
            ->expects($this->never())
            ->method('updateRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->update(1);

        static::assertEquals(403, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::update()
     */
    public function testUpdateRestaurantWhenNullName() : void
    {
        $request = Request::create(
            '/restaurants/1',
            'POST',
            $this->sampleRestaurantAttributes(['name' => null]));
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->never())
            ->method('getUserFromToken');

        $this->restaurantService
            ->expects($this->never())
            ->method('getRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('userCanEditRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('updateRestaurant');

        $this->restaurantController->setRequest($request);
        $response = $this->restaurantController->update(1);

        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::update()
     */
    public function testUpdateRestaurantWhenInvalidHasVegetarian() : void
    {
        $request = Request::create(
            '/restaurants/1',
            'POST',
            $this->sampleRestaurantAttributes(['hasVegetarian' => 'string']));
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->never())
            ->method('getUserFromToken');

        $this->restaurantService
            ->expects($this->never())
            ->method('getRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('userCanEditRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('updateRestaurant');

        $this->restaurantController->setRequest($request);
        $response = $this->restaurantController->update(1);

        static::assertEquals(400, $response->getStatusCode());
    }
}
