<?php

namespace App\Http\Requests;

use App\Models\User\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * Detect user depending on request type.
     *
     * @return User
     */
    public function detectUser()
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
     * @return Gate
     */
    public function gate()
    {
        return Gate::forUser($this->detectUser());
    }
}
