<?php declare(strict_types = 1);

namespace Api\Controllers;

use Api\Exceptions\DeleteRestaurantException;
use Api\Exceptions\RestaurantNotFoundException;
use Api\Exceptions\UserAuthorizationFailedException;
use Api\Exceptions\UserNotFoundException;
use Api\Services\RestaurantService;
use Api\Services\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator;

class RestaurantController extends Controller
{
    /** @var UserService */
    private $userService;

    /** @var RestaurantService */
    private $restaurantService;

    /**
     * RestaurantController constructor.
     * @param UserService $userService
     * @param RestaurantService $restaurantService
     */
    public function __construct(
        UserService $userService,
        RestaurantService $restaurantService
    ) {
        $this->userService = $userService;
        $this->restaurantService = $restaurantService;
    }

    /**
     * @return JsonResponse
     * @throws Validator\Exception\ValidatorException
     * @throws \OutOfBoundsException
     */
    public function all() : JsonResponse
    {
        $validator = Validator\Validation::createValidator();
        $rules = new Validator\Constraints\Collection([
            'from' => new Validator\Constraints\Optional($this->validationRules()['from']),
            'size' => new Validator\Constraints\Optional($this->validationRules()['size']),
            'orderBy' => new Validator\Constraints\Optional($this->validationRules()['orderBy']),
            'order' => new Validator\Constraints\Optional($this->validationRules()['order'])
        ]);

        $errors = $validator->validate($this->request->query->all(), $rules);
        if ($errors->count() > 0) {
            return $this->errorJsonResponse('validationFailed', $errors->get(0)->getMessage());
        }

        $offset = $this->request->query->get('from');
        $size = $this->request->query->get('size');
        $orderBy = $this->request->query->get('orderBy', 'id');
        $order = $this->request->query->get('order', 'asc');

        if ($offset !== null && $size !== null) {
            $restaurants = $this->restaurantService->paginate($size, $offset);
        } else {
            $restaurants = $this->restaurantService->fetchAll($orderBy, $order);
        }

        return $this->jsonResponse(
            true,
            $restaurants,
            [
                'total' => $restaurants->count(),
                'allRestaurantCount' => $this->restaurantService->allRestaurantsCount()
            ]
        );
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restaurant($id) : JsonResponse
    {
        try {
            $restaurant = $this->restaurantService->getRestaurant($id);
        } catch (RestaurantNotFoundException $e) {
            return $this->errorJsonResponse('restaurantNotFound');
        }

        return $this->jsonResponse(true, $restaurant);
    }

    /**
     * @return JsonResponse
     * @throws Validator\Exception\ValidatorException
     * @throws \OutOfBoundsException
     */
    public function create() : JsonResponse
    {
        $validator = Validator\Validation::createValidator();
        $rules = new Validator\Constraints\Collection([
            'name' => new Validator\Constraints\Required($this->validationRules()['name']),
            'hasVegetarian' => new Validator\Constraints\Required($this->validationRules()['hasVegetarian'])
        ]);

        $errors = $validator->validate($this->request->request->all(), $rules);
        if ($errors->count() > 0) {
            return $this->errorJsonResponse('validationFailed', $errors->get(0)->getMessage());
        }

        try {
            $token = $this->getAuthorizationHeader();
            $user = $this->userService->getUserFromToken($token);
        } catch (UserAuthorizationFailedException $e) {
            return $this->errorJsonResponse('authorizationFailed');
        } catch (UserNotFoundException $e) {
            return $this->errorJsonResponse('userNotFound');
        }

        $restaurant = $this->restaurantService->createRestaurant($this->request->request->all(), $user);

        return $this->jsonResponse(true, $restaurant);
    }

    /**
     * @param int $restaurantId
     * @return JsonResponse
     * @throws Validator\Exception\ValidatorException
     * @throws \OutOfBoundsException
     */
    public function update($restaurantId) : JsonResponse
    {
        $validator = Validator\Validation::createValidator();
        $rules = new Validator\Constraints\Collection([
            'name' => new Validator\Constraints\Optional($this->validationRules()['name']),
            'hasVegetarian' => new Validator\Constraints\Optional($this->validationRules()['hasVegetarian'])
        ]);

        $errors = $validator->validate($this->request->request->all(), $rules);
        if ($errors->count() > 0) {
            return $this->errorJsonResponse('validationFailed', $errors->get(0)->getMessage());
        }

        try {
            $token = $this->getAuthorizationHeader();
            $user = $this->userService->getUserFromToken($token);
            $restaurant = $this->restaurantService->getRestaurant($restaurantId);
        } catch (RestaurantNotFoundException $e) {
            return $this->errorJsonResponse('restaurantNotFound');
        } catch (UserAuthorizationFailedException $e) {
            return $this->errorJsonResponse('authorizationFailed');
        } catch (UserNotFoundException $e) {
            return $this->errorJsonResponse('userNotFound');
        }

        if (!$this->restaurantService->userCanEditRestaurant($user, $restaurant)) {
            return $this->errorJsonResponse('unauthorizedAction');
        }

        $attributes = $this->request->request->all();
        $restaurant = $this->restaurantService->updateRestaurant($restaurant, $attributes);

        return $this->jsonResponse(true, $restaurant);
    }

    /**
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function delete($restaurantId) : JsonResponse
    {
        try {
            $token = $this->getAuthorizationHeader();
            $user = $this->userService->getUserFromToken($token);
            $restaurant = $this->restaurantService->getRestaurant($restaurantId);
        } catch (UserAuthorizationFailedException $e) {
            return $this->errorJsonResponse('authorizationFailed');
        } catch (UserNotFoundException $e) {
            return $this->errorJsonResponse('userNotFound');
        } catch (RestaurantNotFoundException $e) {
            return $this->errorJsonResponse('restaurantNotFound');
        }

        if (!$this->restaurantService->userCanEditRestaurant($user, $restaurant)) {
            return $this->errorJsonResponse('unauthorizedAction');
        }

        try {
            $this->restaurantService->deleteRestaurant($restaurant);
        } catch (DeleteRestaurantException $e) {
            return $this->errorJsonResponse('deleteException');
        }

        return $this->jsonResponse(true, []);
    }

    /**
     * @return JsonResponse
     * @throws Validator\Exception\ValidatorException
     * @throws \OutOfBoundsException
     */
    public function search() : JsonResponse
    {
        $validator = Validator\Validation::createValidator();
        $rules = new Validator\Constraints\Collection([
            'name' => new Validator\Constraints\Optional($this->validationRules()['name']),
            'hasVegetarian' => new Validator\Constraints\Optional($this->validationRules()['hasVegetarian']),
            'orderBy' => new Validator\Constraints\Optional($this->validationRules()['orderBy']),
            'order' => new Validator\Constraints\Optional($this->validationRules()['order'])
        ]);

        $errors = $validator->validate($this->request->query->all(), $rules);
        if ($errors->count() > 0) {
            return $this->errorJsonResponse('validationFailed', $errors->get(0)->getMessage());
        }

        $searchParams = [
            'name' => $this->request->query->get('name', ''),
            'orderBy' => $this->request->query->get('orderBy', 'id'),
            'order' => $this->request->query->get('order', 'asc'),
            'hasVegetarian' => $this->request->query->get('hasVegetarian')
        ];

        $restaurants = $this->restaurantService->searchRestaurants($searchParams);

        return $this->jsonResponse(
            true,
            $restaurants,
            ['total' => $restaurants->count()]
        );
    }

    /**
     * @return array
     * @throws Validator\Exception\ValidatorException
     */
    private function validationRules() : array
    {
        return [
            'name' => [
                new Validator\Constraints\NotBlank(),
                new Validator\Constraints\NotNull(),
                new Validator\Constraints\Type([
                    'type' => 'string',
                    'message' => 'Invalid name'
                ])
            ],
            'hasVegetarian' => [
                new Validator\Constraints\NotNull(),
                new Validator\Constraints\Choice([
                    'choices' => ['true', 'false'],
                    'message' => 'Invalid vegetarian value'
                ])
            ],
            'from' => [
                new Validator\Constraints\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'from should be greater than or equal to 0'
                ])
            ],
            'size' => [
                new Validator\Constraints\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'size value should be greater than or equal to 0'
                ])
            ],
            'orderBy' => [
                new Validator\Constraints\Choice([
                    'choices' => $this->orderByValues(),
                    'message' => 'Invalid orderBy value'
                ])
            ],
            'order' => [
                new Validator\Constraints\Choice([
                    'choices' => ['asc', 'desc'],
                    'message' => 'Invalid order value'
                ])
            ]
        ];
    }

    /**
     * @return string
     * @throws UserAuthorizationFailedException
     */
    private function getAuthorizationHeader() : string
    {
        $token = $this->request->headers->get('authorization');
        if ($token === null) {
            throw new UserAuthorizationFailedException('No authorization token supplied');
        }

        return $token;
    }

    /**
     * @param string $errorType
     * @param string $customMessage
     * @return JsonResponse
     */
    private function errorJsonResponse($errorType, $customMessage = '')
    {
        switch ($errorType) {
            case 'userNotFound':
                $message = 'User not found';
                $code = $this->response::HTTP_UNAUTHORIZED;
                break;
            case 'authorizationFailed':
                $message = 'User authorization failed';
                $code = $this->response::HTTP_UNAUTHORIZED;
                break;
            case 'validationFailed':
                $message = $customMessage;
                $code = $this->response::HTTP_BAD_REQUEST;
                break;
            case 'restaurantNotFound':
                $message = 'Restaurant not found';
                $code = $this->response::HTTP_BAD_REQUEST;
                break;
            case 'unauthorizedAction':
                $message = 'User is not authorized to do this action';
                $code = $this->response::HTTP_FORBIDDEN;
                break;
            default :
                $message = 'There was an error with the request';
                $code = $this->response::HTTP_BAD_REQUEST;
        }

        return $this->jsonResponse(false, [], ['message' => $message], $code);
    }

    /**
     * @return array
     */
    private function orderByValues() : array
    {
        return ['id', 'name', 'difficulty', 'prepTime'];
    }
}
