<?php

namespace App\Models\User;

use App\Exceptions\UserCreatedWithAnotherDriverException;
use Hash;
use Laravel\Socialite\Two\User as SocialiteUser;

class UserRepository
{
    /**
     * @param array $attributes
     * @param string $authDriver
     * @return User
     */
    public function create(array $attributes, string $authDriver = null) : User
    {
        $user = new User($attributes);

        // generate api token
        $user->api_token = md5(uniqid());

        // set auth_driver or hash password
        if ($authDriver) {
            $user->password = '';
            $user->auth_driver = $authDriver;
        } else {
            $user->password = Hash::make($attributes['password']);
        }

        $user->save();

        return $user;
    }

    /**
     * @param string $email
     * @param string $password
     * @return User
     */
    public function findByCredentials(string $email, string $password)
    {
        $user = User::whereEmail($email)->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }
    }

    /**
     * Check if matching user exists. Return it or create new.
     *
     * @param SocialiteUser $socialiteUser
     * @param string $authDriver
     * @return User
     * @throws UserCreatedWithAnotherDriverException
     */
    public function handleSocialiteUser(SocialiteUser $socialiteUser, string $authDriver) : User
    {
        $user = User::whereEmail($socialiteUser->email)->first();

        // user does not exist - create and return user
        if (!$user) {
            return $this->create([
                'email' => $socialiteUser->email,
            ], $authDriver);
        }

        // user exists, and was created with the same driver - return user
        if ($user->auth_driver == $authDriver) {
            return $user;
        }

        // user exists, but was not created with the same driver - error
        if ($user->auth_driver != $authDriver) {
            throw new UserCreatedWithAnotherDriverException($user);
        }
    }
}
