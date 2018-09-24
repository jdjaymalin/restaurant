<?php namespace Tests\Unit\Controller;

use Api\Exceptions\RestaurantNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class GetRestaurantTest extends AbstractRestaurantController
{
    /**
     * @covers \Api\Controllers\RestaurantController::restaurant()
     */
    public function testGetRestaurant() : void
    {
        $this->restaurantService
            ->expects($this->once())
            ->method('getRestaurant')
            ->with('1')
            ->willReturn($this->sampleRestaurant());

        $request = Request::create('/restaurants/1');
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->restaurant(1);

        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::restaurant()
     */
    public function testGetRestaurantDoesNotExist() : void
    {
        $this->restaurantService
            ->expects($this->once())
            ->method('getRestaurant')
            ->with('1')
            ->willThrowException(new RestaurantNotFoundException());

        $request = Request::create('/restaurants/1');
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->restaurant(1);

        static::assertEquals(400, $response->getStatusCode());
    }
}
