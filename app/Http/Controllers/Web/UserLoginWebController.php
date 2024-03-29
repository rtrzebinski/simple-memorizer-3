<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class UserLoginWebController extends WebController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * The user has been authenticated.
     *
     * @param Request $request
     * @param mixed $user
     * @return mixed|void
     */
    protected function authenticated(Request $request, mixed $user)
    {
        if ($request->session()->get('subscribe-lesson-id')) {
            /** @var Lesson $lesson */
            $lesson = Lesson::find($request->session()->get('subscribe-lesson-id'));
            $isSubscriber = $lesson->subscribedUsers()->where(['users.id' => $user->id])->exists();
            if (!$isSubscriber) {
                $lesson->subscribe($user);
            }
            return redirect($request->session()->get('subscribe-redirect-url'));
        }
    }
}
