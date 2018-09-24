<?php namespace Api\Services;

use Api\Models\Restaurant;
use Api\Models\User;
use Api\Exceptions;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class RestaurantService
{
    /**
     * @param array $attributes
     * @param User $user
     * @return Restaurant
     */
    public function createRestaurant(array $attributes, User $user) : Restaurant
    {
        $restaurant = new Restaurant([
            'name' => $attributes['name'],
            'hasVegetarian' => $attributes['hasVegetarian']
        ]);

        $restaurant->author()->associate($user);
        $restaurant->save();

        return $restaurant;
    }

    /**
     * @param Restaurant $restaurant
     * @param array $attributes
     * @return Restaurant
     */
    public function updateRestaurant(Restaurant $restaurant, array $attributes) : Restaurant
    {
        $attributes = array_filter($attributes);
        $restaurant->update($attributes);

        return Restaurant::find($restaurant->id);
    }

    /**
     * @param $restaurantId
     * @return Restaurant
     * @throws Exceptions\RestaurantNotFoundException
     */
    public function getRestaurant($restaurantId) : Restaurant
    {
        $restaurant = Restaurant::find($restaurantId)->first();
        if (!$restaurant) {
            throw new Exceptions\RestaurantNotFoundException('Restaurant not found');
        }

        return $restaurant;
    }

    /**
     * @param User $user
     * @param Restaurant $restaurant
     * @return bool
     */
    public function userCanEditRestaurant(User $user, Restaurant $restaurant) : bool
    {
        return $user->id === $restaurant->user_id;
    }

    /**
     * @param string $orderBy
     * @param string $order
     * @return Collection
     */
    public function fetchAll($orderBy, $order) : Collection
    {
        return Restaurant::orderBy($orderBy, $order)->get();
    }

    /**
     * @param $size
     * @param $offset
     * @return Collection
     */
    public function paginate($size, $offset) : Collection
    {
        return Restaurant::skip($offset)->take($size)->get();
    }

    /**
     * @param Restaurant $restaurant
     * @throws Exceptions\DeleteRestaurantException
     */
    public function deleteRestaurant(Restaurant $restaurant) : void
    {
        try {
            $restaurant->delete();
        } catch (Exception $e) {
            throw new Exceptions\DeleteRestaurantException('Unable to delete restaurant');
        }
    }

    /**
     * @param array $searchParams
     * @return Restaurant[]|Collection
     */
    public function searchRestaurants(array $searchParams)
    {
        $restaurantQuery = Restaurant::nameSearch($searchParams['name'])
            ->orderBy($searchParams['orderBy'], $searchParams['order']);


        if ($searchParams['hasVegetarian'] !== null) {
            $restaurantQuery = $restaurantQuery->hasVegetarian($searchParams['hasVegetarian']);
        }

        return $restaurantQuery->get();
    }

    /**
     * @return int
     */
    public function allRestaurantsCount() : int
    {
        return Restaurant::all()->count();
    }
}
