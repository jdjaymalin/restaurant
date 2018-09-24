<?php namespace Tests\Unit\Controller;

use Api\Exceptions\DeleteRestaurantException;
use Api\Exceptions\RestaurantNotFoundException;
use Api\Exceptions\UserAuthorizationFailedException;
use Api\Exceptions\UserNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class DeleteRestaurantTest extends AbstractRestaurantController
{
    /**
     * @covers \Api\Controllers\RestaurantController::delete()
     */
    public function testDeleteRestaurant() : void
    {
        $request = Request::create('/restaurants/1', 'DELETE');
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
            ->method('deleteRestaurant')
            ->with($this->sampleRestaurant())
            ->willReturn($this->sampleRestaurant());

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->delete(1);

        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::delete()
     */
    public function testDeleteRestaurantNoAuthorizationToken() : void
    {
        $request = Request::create('/restaurants/1', 'DELETE');
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
            ->method('deleteRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->delete(1);

        static::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::delete()
     */
    public function testDeleteRestaurantInvalidToken() : void
    {
        $request = Request::create('/restaurants/1', 'DELETE');
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
            ->method('deleteRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->delete(1);

        static::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::delete()
     */
    public function testDeleteRestaurantUserNotFound() : void
    {
        $request = Request::create('/restaurants/1', 'DELETE');
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
            ->method('deleteRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->delete(1);

        static::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::delete()
     */
    public function testDeleteRestaurantWhenRestaurantNotFound() : void
    {
        $request = Request::create('/restaurants/1', 'DELETE');
        $request->headers->set('authorization', 'jwttoken');

        $this->userService
            ->expects($this->once())
            ->method('getUserFromToken')
            ->willReturn($this->sampleUser());

        $this->restaurantService
            ->expects($this->once())
            ->method('getRestaurant')
            ->willThrowException(new RestaurantNotFoundException);

        $this->restaurantService
            ->expects($this->never())
            ->method('userCanEditRestaurant');

        $this->restaurantService
            ->expects($this->never())
            ->method('deleteRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->delete(1);

        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::delete()
     */
    public function testDeleteRestaurantWhenUserDoesNotHaveAccess() : void
    {
        $request = Request::create('/restaurants/1', 'DELETE');
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
            ->method('deleteRestaurant');

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->delete(1);

        static::assertEquals(403, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::delete()
     */
    public function testDeleteRestaurantWhereDeleteFailed() : void
    {
        $request = Request::create('/restaurants/1', 'DELETE');
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
            ->method('deleteRestaurant')
            ->with($this->sampleRestaurant())
            ->willThrowException(new DeleteRestaurantException);

        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->delete(1);

        static::assertEquals(400, $response->getStatusCode());
    }
}
