<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate as GateFacade;

abstract class Request extends FormRequest
{
    /**
     * Detect user depending on request type.
     *
     * @return User
     */
    private function detectUser(): User
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
    protected function gate(): Gate
    {
        return GateFacade::forUser($this->detectUser());
    }
}
