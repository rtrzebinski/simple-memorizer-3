<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\UserCreatedWithAnotherDriverException;
use App\Repositories\UserRepository;
use Auth;
use Illuminate\Support\MessageBag;
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
 * Tested drivers: facebook, google, github
 *
 * @see https://github.com/laravel/socialite
 * @package App\Http\Controllers\Web
 */
class SocialiteController extends Controller
{
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

        try {

            $user = $userRepository->handleSocialiteUser($socialiteUser, $authDriver);

        } catch (UserCreatedWithAnotherDriverException $e) {

            $message = sprintf("User with email '%s' exists, but was not created with %s.", $socialiteUser->email,
                $authDriver);
            if ($e->user->auth_driver) {
                $message .= sprintf(' Try to login with %s.', $e->user->auth_driver);
            } else {
                $message .= ' Try to login using email and password.';
            }

            return redirect('/login')->with('errors', new MessageBag([$message]));
        }

        Auth::login($user);

        return redirect('/home');
    }
}
