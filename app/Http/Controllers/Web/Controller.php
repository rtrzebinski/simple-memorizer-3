<?php

namespace App\Http\Controllers\Web;

use App\Models\User\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * User authenticated via web interface.
     * @return User
     */
    protected function user()
    {
        return Auth::guard('web')->user();
    }
}
