<?php

namespace App\Models\User;

use Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @param array $attributes
     * @return User
     */
    public function create(array $attributes) : User
    {
        $user = new User($attributes);
        $user->password = Hash::make($attributes['password']);
        $user->api_token = md5(uniqid());
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
}
