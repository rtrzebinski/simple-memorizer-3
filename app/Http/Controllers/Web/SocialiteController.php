<?php

namespace App\Http\Controllers\Web;

use App\Models\User\UserRepository;
use Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Laravel\Socialite\Two\User;
use Socialite;

/**
 * Class SocialiteController
 *
 * Laravel Socialite provides an expressive,
 * fluent interface to OAuth authentication with Facebook,
 * Twitter, Google, LinkedIn, GitHub and Bitbucket.
 * It handles almost all of the boilerplate social authentication code you are dreading writing.
 *
 * Tested drivers: facebook
 *
 * @see https://github.com/laravel/socialite
 * @package App\Http\Controllers\Web
 */
class SocialiteController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * @param string $authDriver
     * @return RedirectResponse
     */
    public function redirectToProvider(string $authDriver) : RedirectResponse
    {
        return Socialite::driver($authDriver)->redirect();
    }

    /**
     * @param string $authDriver
     * @param UserRepository $userRepository
     * @return RedirectResponse
     */
    public function handleProviderCallback(string $authDriver, UserRepository $userRepository) : RedirectResponse
    {
        /** @var User $socialiteUser */
        $socialiteUser = Socialite::driver($authDriver)->user();

        $user = $userRepository->handleSocialiteUser($socialiteUser, $authDriver);

        Auth::login($user);

        return redirect($this->redirectTo);
    }
}
