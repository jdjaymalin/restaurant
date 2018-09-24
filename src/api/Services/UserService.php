<?php namespace Api\Services;

use Api\Exceptions\UserAuthorizationFailedException;
use Api\Exceptions\UsernameIsTakenException;
use Api\Exceptions\UserNotFoundException;
use Api\Models\User;
use Exception;
use Firebase\JWT\JWT;

class UserService
{
    /** @var string */
    private $jwtKey;

    /** @var string */
    private $jwtAlgo;

    public function __construct()
    {
        $this->jwtKey = env('JWT_Key');
        $this->jwtAlgo = env('JWT_Algo');
    }

    /**
     * @param User $user
     * @return string
     */
    public function generateJWTToken(User $user) : string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 60;
        $payload = array(
            'userId' => $user->id,
            'iat' => $issuedAt,
            'exp' => $expirationTime
        );

        return JWT::encode($payload, $this->jwtKey, $this->jwtAlgo);
    }

    /**
     * @param $token
     * @return User
     *
     * @throws UserNotFoundException
     * @throws UserAuthorizationFailedException
     */
    public function getUserFromToken($token) : User
    {
        try {
            $data = JWT::decode($token, $this->jwtKey, [$this->jwtAlgo]);
            $userId = $data->userId;
            $user = User::find($userId)->first();

            if (!$user) {
                throw new UserNotFoundException("User with id $userId not found");
            }

            return $user;
        } catch (Exception $e) {
            throw new UserAuthorizationFailedException('Unable to authorize user with given token');
        }
    }

    /**
     * @param string $username
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserByUserName($username) : User
    {
        $user = User::where('username', $username)->first();
        if ($user === null) {
            throw new UserNotFoundException('User not found');
        }

        return $user;
    }

    /**
     * @param User $user
     * @param string $password
     * @return bool
     */
    private function isValidCredentials(User $user, $password) : bool
    {
        return password_verify($password, $user->password);
    }

    /**
     * @param User $user
     * @param string $password
     * @return string
     */
    public function validateUserAndGetToken(User $user, $password) : string
    {
        if (!$this->isValidCredentials($user, $password)) {
            throw new UserAuthorizationFailedException('Invalid credentials');
        }

        return $this->generateJWTToken($user);
    }

    /**
     * @param array $attributes
     * @return User
     */
    public function createUser(array $attributes) : User
    {
        $user = $this->findUserByUserName($attributes['username']);
        if ($user) {
            throw new UsernameIsTakenException('Username is already taken');
        }

        return User::create([
            'username' => $attributes['username'],
            'password' => password_hash($attributes['password'], PASSWORD_BCRYPT),
            'firstName' => $attributes['firstName'],
            'lastName' => $attributes['lastName']
        ]);
    }
}
