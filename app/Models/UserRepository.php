<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;

class UserRepository
{
    /**
     * @param array $attributes
     * @return User
     */
    public function create(array $attributes): User
    {
        $user = new User($attributes);

        // generate api token
        $user->api_token = md5(uniqid());
        // hash password
        $user->password = Hash::make($attributes['password']);
        $user->save();

        return $user;
    }

    /**
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function findByCredentials(string $email, string $password): ?User
    {
        /** @var User $user */
        $user = User::whereEmail($email)->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }
}
