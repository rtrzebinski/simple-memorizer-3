<?php

namespace App\Models\User;

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
     * Check if matching user exists in our database. Return it or create new.
     *
     * @param SocialiteUser $socialiteUser
     * @param string $authDriver
     * @return User
     */
    public function handleSocialiteUser(SocialiteUser $socialiteUser, string $authDriver) : User
    {
        $user = User::whereEmail($socialiteUser->email)
            ->whereAuthDriver($authDriver)
            ->first();

        if ($user) {
            return $user;
        }

        return $this->create([
            'email' => $socialiteUser->email,
        ], $authDriver);
    }
}
