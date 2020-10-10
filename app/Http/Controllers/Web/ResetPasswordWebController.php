<?php

namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordWebController extends WebController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;
}
