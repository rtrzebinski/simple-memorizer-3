<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson;
use App\Models\UserRepository;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class UserRegisterWebController extends WebController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     * @param UserRepository $userRepository
     */
    public function __construct(private UserRepository $userRepository)
    {
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data,
            [
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $attributes
     * @return User
     */
    protected function create(array $attributes)
    {
        return $this->userRepository->create($attributes);
    }

    /**
     * The user has been registered.
     *
     * @param Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function registered(Request $request, mixed $user)
    {
        if ($request->session()->get('subscribe-lesson-id')) {
            /** @var Lesson $lesson */
            $lesson = Lesson::find($request->session()->get('subscribe-lesson-id'));
            $lesson->subscribe($user);
            return redirect($request->session()->get('subscribe-redirect-url'));
        }
    }
}
