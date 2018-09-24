<?php namespace Tests\Unit\Controller;

use Api\Exceptions\UserAuthorizationFailedException;
use Api\Exceptions\UserNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class AuthUserTest extends AbstractUserControllerTest
{
    /**
     * @covers \Api\Controllers\UserController::auth()
     */
    public function testAuthUser() : void
    {
        $request = Request::create('/user', 'POST', $this->sampleUserAttributes());
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->once())
            ->method('findUserByUserName')
            ->with($this->sampleUserAttributes()['username'])
            ->willReturn($this->sampleUser());

        $jwtToken = 'jwt_token';
        $this->userService
            ->expects($this->once())
            ->method('validateUserAndGetToken')
            ->with($this->sampleUser(), $this->sampleUserAttributes()['password'])
            ->willReturn($jwtToken);

        $response = $this->userController->auth();
        $responseBody = json_decode($response->getContent());

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals($responseBody->token, $jwtToken);
    }

    /**
     * @covers \Api\Controllers\UserController::auth()
     */
    public function testAuthUserNoUserFound() : void
    {
        $request = Request::create('/user', 'POST', $this->sampleUserAttributes());
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->once())
            ->method('findUserByUserName')
            ->with($this->sampleUserAttributes()['username'])
            ->willThrowException(new UserNotFoundException);

        $this->userService
            ->expects($this->never())
            ->method('validateUserAndGetToken');

        $response = $this->userController->auth();

        static::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\UserController::auth()
     */
    public function testAuthUserInvalidPassword() : void
    {
        $request = Request::create(
            '/user',
            'POST',
            $this->sampleUserAttributes(['password' => 'invalidPassword']));
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->once())
            ->method('findUserByUserName')
            ->with($this->sampleUserAttributes()['username'])
            ->willReturn($this->sampleUser());

        $this->userService
            ->expects($this->once())
            ->method('validateUserAndGetToken')
            ->with($this->sampleUser(), 'invalidPassword')
            ->willThrowException(new UserAuthorizationFailedException);

        $response = $this->userController->auth();

        static::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\UserController::auth()
     */
    public function testAuthUserBlankUsername() : void
    {
        $request = Request::create(
            '/user',
            'POST',
            $this->sampleUserAttributes(['username' => '']));
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->never())
            ->method('findUserByUserName');

        $this->userService
            ->expects($this->never())
            ->method('validateUserAndGetToken');

        $response = $this->userController->auth();

        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @covers \Api\Controllers\UserController::auth()
     */
    public function testAuthUserBlankPassword() : void
    {
        $request = Request::create(
            '/user',
            'POST',
            $this->sampleUserAttributes(['password' => '']));
        $this->userController->setRequest($request);

        $this->userService
            ->expects($this->never())
            ->method('findUserByUserName');

        $this->userService
            ->expects($this->never())
            ->method('validateUserAndGetToken');

        $response = $this->userController->auth();

        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function sampleUserAttributes(array $attributes = []) : array
    {
        return array_merge([
            'username' => 'test_user',
            'password' => 'passwordHash'
        ], $attributes);
    }
}
