<?php

namespace App\Exceptions;

use App\Models\User\User;
use Exception;

class UserCreatedWithAnotherDriverException extends Exception
{
    /**
     * @var User
     */
    public $user;

    /**
     * UserAlreadyExistsException constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
