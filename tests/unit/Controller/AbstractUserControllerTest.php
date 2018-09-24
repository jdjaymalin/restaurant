<?php namespace Tests\unit\Controller;

use Api\Controllers\UserController;
use Api\Models\User;
use Api\Services\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractUserControllerTest extends TestCase
{
    /** @var MockObject|UserService */
    protected $userService;

    /** @var UserController */
    protected $userController;

    public function setUp()
    {
        $this->userService = $this->createMock(UserService::class);
        $this->userController = new UserController($this->userService);

        $jsonResponse = JsonResponse::create();
        $this->userController->setResponse($jsonResponse);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function sampleUserAttributes(array $attributes = []) : array
    {
        return array_merge([
            'username' => 'test_user',
            'firstName' => 'Test',
            'lastName' => 'Test',
            'password' => 'passwordHash'
        ], $attributes);
    }

    /**
     * @return User
     */
    protected function sampleUser() : User
    {
        return new User([
            'username' => 'test_user',
            'firstName' => 'Test',
            'lastName' => 'Test'
        ]);
    }
}
