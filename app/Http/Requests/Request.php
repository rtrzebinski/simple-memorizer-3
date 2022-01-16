<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * Detect user depending on request type.
     *
     * @return User
     */
    public function detectUser(): User
    {
        if ($this->ajax() || $this->wantsJson()) {
            return $this->user('api');
        } else {
            return $this->user('web');
        }
    }

    /**
     * Get a guard instance for the current user.
     *
     * @return \Illuminate\Auth\Access\Gate
     */
    public function gate(): \Illuminate\Auth\Access\Gate
    {
        return Gate::forUser($this->detectUser());
    }
}
