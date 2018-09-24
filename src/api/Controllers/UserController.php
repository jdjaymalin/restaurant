<?php declare(strict_types = 1);

namespace Api\Controllers;

use Api\Exceptions\UserAuthorizationFailedException;
use Api\Exceptions\UsernameIsTakenException;
use Api\Exceptions\UserNotFoundException;
use Api\Services\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator;

class UserController extends Controller
{
    /** @var UserService */
    private $userService;

    /**
     * UserController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
            'username' => new Validator\Constraints\Required($this->validationRules()['username']),
            'password' => new Validator\Constraints\Required($this->validationRules()['password']),
            'firstName' => new Validator\Constraints\Required($this->validationRules()['firstName']),
            'lastName' => new Validator\Constraints\Required($this->validationRules()['lastName'])
        ]);

        $errors = $validator->validate($this->request->request->all(), $rules);
        if ($errors->count() > 0) {
            return $this->errorJsonResponse('validationFailed', $errors->get(0)->getMessage());
        }

        try {
            $user = $this->userService->createUser($this->request->request->all());
            $jwtToken = $this->userService->generateJWTToken($user);
        } catch (UsernameIsTakenException $e) {
            return $this->errorJsonResponse('userNameIsTaken');
        }

        return $this->jsonResponse(true, $user, ['token' => $jwtToken], 200);
    }

    /**
     * @return JsonResponse
     * @throws Validator\Exception\ValidatorException
     * @throws \OutOfBoundsException
     */
    public function auth() : JsonResponse
    {
        $validator = Validator\Validation::createValidator();
        $rules = new Validator\Constraints\Collection([
            'username' => new Validator\Constraints\Required($this->validationRules()['username']),
            'password' => new Validator\Constraints\Required($this->validationRules()['password'])
        ]);

        $errors = $validator->validate($this->request->request->all(), $rules);
        if ($errors->count() > 0) {
            return $this->errorJsonResponse('validationFailed', $errors->get(0)->getMessage());
        }

        $userName = $this->request->request->get('username');
        $password = $this->request->request->get('password');

        try {
            $user = $this->userService->findUserByUserName($userName);
            $token = $this->userService->validateUserAndGetToken($user, $password);
            return $this->jsonResponse(true, $user, ['token' => $token], 200);
        } catch (UserNotFoundException $e) {
            return $this->errorJsonResponse('authorizationFailed');
        } catch (UserAuthorizationFailedException $e) {
            return $this->errorJsonResponse('authorizationFailed');
        }
    }

    /**
     * @param $errorType
     * @param string $customMessage
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function errorJsonResponse($errorType, $customMessage = '') : JsonResponse
    {
        switch ($errorType) {
            case 'userNameIsTaken':
                $message = 'Username is already taken';
                $code = $this->response::HTTP_BAD_REQUEST;
                break;
            case 'authorizationFailed':
                $message = 'User authorization failed';
                $code = $this->response::HTTP_UNAUTHORIZED;
                break;
            case 'validationFailed':
                $message = $customMessage;
                $code = $this->response::HTTP_BAD_REQUEST;
                break;
            default :
                $message = 'There was an error with the request';
                $code = $this->response::HTTP_BAD_REQUEST;
        }

        return $this->response->setData([
            'success' => false,
            'message' => $message
        ])->setStatusCode($code);
    }

    /**
     * @return array
     * @throws Validator\Exception\ValidatorException
     */
    private function validationRules() : array
    {
        return [
            'username' => [
                new Validator\Constraints\NotBlank(),
                new Validator\Constraints\NotNull()
            ],
            'password' => [
                new Validator\Constraints\NotBlank(),
                new Validator\Constraints\NotNull()
            ],
            'firstName' => [
                new Validator\Constraints\NotBlank(),
                new Validator\Constraints\NotNull()
            ],
            'lastName' => [
                new Validator\Constraints\NotBlank(),
                new Validator\Constraints\NotNull()
            ]
        ];
    }
}
