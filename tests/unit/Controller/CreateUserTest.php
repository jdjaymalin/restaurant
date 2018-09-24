<?php namespace Tests\Unit\Controller;

use Symfony\Component\HttpFoundation\Request;

class CreateUserTest extends AbstractUserControllerTest
{
    /**
     * @covers \Api\Controllers\UserController::create()
     */
    public function testCreateUser() : void
    {
        $request = Request::create('/user', 'POST', $this->sampleUserAttributes());
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->once())
            ->method('createUser')
            ->with($this->sampleUserAttributes())
            ->willReturn($this->sampleUser());

        $jwtToken = 'jwt_token';
        $this->userService
            ->expects($this->once())
            ->method('generateJWTToken')
            ->with($this->sampleUser())
            ->willReturn($jwtToken);

        $response = $this->userController->create();
        $responseBody = json_decode($response->getContent());

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals($responseBody->token, $jwtToken);
    }

    /**
     * @covers \Api\Controllers\UserController::create()
     */
    public function testCreateRestaurantInvalidUsernameBlank() : void
    {
        $request = Request::create('/user', 'POST', $this->sampleUserAttributes(['username' => '']));
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->never())
            ->method('createUser');

        $this->userService
            ->expects($this->never())
            ->method('generateJWTToken');

        $response = $this->userController->create();
        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\UserController::create()
     */
    public function testCreateRestaurantInvalidPasswordBlank() : void
    {
        $request = Request::create('/user', 'POST', $this->sampleUserAttributes(['password' => '']));
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->never())
            ->method('createUser');

        $this->userService
            ->expects($this->never())
            ->method('generateJWTToken');

        $response = $this->userController->create();
        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\UserController::create()
     */
    public function testCreateRestaurantInvalidFirstNameBlank() : void
    {
        $request = Request::create('/user', 'POST', $this->sampleUserAttributes(['firstName' => '']));
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->never())
            ->method('createUser');

        $this->userService
            ->expects($this->never())
            ->method('generateJWTToken');

        $response = $this->userController->create();
        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\UserController::create()
     */
    public function testCreateRestaurantInvalidLastNameBlank() : void
    {
        $request = Request::create('/user', 'POST', $this->sampleUserAttributes(['lastName' => '']));
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->never())
            ->method('createUser');

        $this->userService
            ->expects($this->never())
            ->method('generateJWTToken');

        $response = $this->userController->create();
        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\UserController::create()
     */
    public function testCreateRestaurantMissingParams() : void
    {
        $request = Request::create('/user', 'POST', []);
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->never())
            ->method('createUser');

        $this->userService
            ->expects($this->never())
            ->method('generateJWTToken');

        $response = $this->userController->create();
        static::assertEquals(400, $response->getStatusCode());
    }
}
