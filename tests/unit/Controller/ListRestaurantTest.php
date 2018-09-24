<?php namespace Tests\Unit\Controller;

use Symfony\Component\HttpFoundation\Request;

class ListRestaurantTest extends AbstractRestaurantController
{
    /**
     * @covers \Api\Controllers\RestaurantController::all()
     */
    public function testListRestaurants() : void
    {
        $this->restaurantService
            ->expects($this->once())
            ->method('fetchAll')
            ->with('id', 'asc');

        $request = Request::create('/restaurants');
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->all();

        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::all()
     */
    public function testListRestaurantsWithPagination() : void
    {
        $this->restaurantService
            ->expects($this->once())
            ->method('paginate')
            ->with('1', '0');

        $request = Request::create('/restaurants?from=0&size=1');
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->all();

        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::all()
     */
    public function testListRestaurantsWithOrderBy() : void
    {
        $this->restaurantService
            ->expects($this->once())
            ->method('fetchAll')
            ->with('name', 'asc');

        $request = Request::create('/restaurants?orderBy=name');
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->all();

        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::all()
     */
    public function testListRestaurantsWithInvalidOrderBy() : void
    {
        $this->restaurantService
            ->expects($this->never())
            ->method('fetchAll');

        $request = Request::create('/restaurants?orderBy=invalidValue');
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->all();

        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::all()
     */
    public function testListRestaurantsWithIncorrectPaginationParams() : void
    {
        $this->restaurantService
            ->expects($this->never())
            ->method('paginate');

        $this->restaurantService
            ->expects($this->never())
            ->method('fetchAll');

        $request = Request::create('/restaurants?from=-1&size=-1');
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->all();

        static::assertEquals(400, $response->getStatusCode());
    }
}
