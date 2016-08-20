<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * @return int
     */
    protected function userId() : int
    {
        return $this->user('api')->id;
    }
}
