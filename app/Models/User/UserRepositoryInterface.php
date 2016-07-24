<?php

namespace App\Models\User;

interface UserRepositoryInterface
{
    public function create(array $attributes) : User;

    public function findByCredentials(string $email, string $password);
}
