<?php namespace Tests\Unit\Controller;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;

class SearchRestaurantTest extends AbstractRestaurantController
{
    /**
     * @covers \Api\Controllers\RestaurantController::search()
     */
    public function testSearchRestaurants() : void
    {
        $this->restaurantService
            ->expects($this->once())
            ->method('searchRestaurants')
            ->with($this->searchParams())
            ->willReturn(new Collection());

        $uri = '/restaurants/search?' . http_build_query($this->searchParams());
        $request = Request::create($uri);
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->search();

        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::search()
     */
    public function testSearchRestaurantsInvalidOrderBy() : void
    {
        $this->restaurantService
            ->expects($this->never())
            ->method('searchRestaurants');

        $uri = '/restaurants/search?' . http_build_query($this->searchParams(['orderBy' => 'invalid']));
        $request = Request::create($uri);
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->search();

        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\RestaurantController::search()
     */
    public function testSearchRestaurantsNullName() : void
    {
        $this->restaurantService
            ->expects($this->once())
            ->method('searchRestaurants')
            ->with($this->searchParams(['name' => '']))
            ->willReturn(new Collection());

        $uri = '/restaurants/search?' . http_build_query($this->searchParams(['name' => null]));
        $request = Request::create($uri);
        $this->restaurantController->setRequest($request);

        $response = $this->restaurantController->search();

        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @param array $params
     * @return array
     */
    private function searchParams(array $params = []) : array
    {
        return array_merge([
            'name' => 'Honey Chicken',
            'orderBy' => 'id',
            'order' => 'asc',
            'hasVegetarian' => null
        ], $params);
    }
}
