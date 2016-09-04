<?php

namespace App\Http\Requests;

use Gate as GateFacade;
use Illuminate\Auth\Access\Gate as AccessGate;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * @return AccessGate
     */
    protected function gate() : AccessGate
    {
        return GateFacade::forUser($this->user('api'));
    }
}
