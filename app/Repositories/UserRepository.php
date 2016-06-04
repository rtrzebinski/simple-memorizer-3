<?php

namespace App\Repositories;

use App\User;
use Hash;

class UserRepository
{
    /**
     * @param array $data
     * @return User
     */
    public function create(array $data)
    {
        $user = new User();
        $user->fill($data);
        $user->password = Hash::make($data['password']);
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
