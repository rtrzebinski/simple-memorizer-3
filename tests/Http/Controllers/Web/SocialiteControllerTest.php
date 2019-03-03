<?php

namespace Tests\Http\Controllers\Web;

use App\Exceptions\UserCreatedWithAnotherDriverException;
use App\Repositories\UserRepository;
use Auth;
use Laravel\Socialite\Two\AbstractProvider;
use Socialite;
use Illuminate\Http\RedirectResponse;

class SocialiteControllerTest extends BaseTestCase
{
    public function testItShould_redirectToProvider()
    {
        $redirectResponse = new RedirectResponse($redirectUrl = $this->randomUrl());

        // mock provider
        $provider = $this->createMock(AbstractProvider::class);
        $provider->method('redirect')->willReturn($redirectResponse);

        // mock facade
        Socialite::shouldReceive('driver')
            ->with($driver = uniqid())
            ->andReturn($provider);

        $this->call('GET', '/login/' . $driver);

        $this->assertRedirectedTo($redirectUrl);
    }

    public function testItShould_handleProviderCallback()
    {
        $socialiteUser = $this->createSocialiteUser();
        $user = $this->createUser();

        // mock provider
        $provider = $this->createMock(AbstractProvider::class);
        $provider->method('user')->willReturn($socialiteUser);

        // mock facade
        Socialite::shouldReceive('driver')
            ->with($driver = uniqid())
            ->andReturn($provider);

        $userRepository = $this->createMock(UserRepository::class);
        $this->app->instance(UserRepository::class, $userRepository);
        $userRepository->method('handleSocialiteUser')
            ->with($socialiteUser, $driver)
            ->willReturn($user);

        $this->call('GET', '/login/callback/' . $driver);

        $this->assertEquals($user, Auth::user());
        $this->assertRedirectedTo('/home');
    }

    public function testItShould_handleProviderCallback_regularUserAlreadyExists()
    {
        $socialiteUser = $this->createSocialiteUser();
        $user = $this->createUser();

        // mock provider
        $provider = $this->createMock(AbstractProvider::class);
        $provider->method('user')->willReturn($socialiteUser);

        // mock facade
        Socialite::shouldReceive('driver')
            ->with($driver = uniqid())
            ->andReturn($provider);

        $userRepository = $this->createMock(UserRepository::class);
        $this->app->instance(UserRepository::class, $userRepository);
        $userRepository->method('handleSocialiteUser')
            ->with($socialiteUser, $driver)
            ->willThrowException(new UserCreatedWithAnotherDriverException($user));

        $this->call('GET', '/login/callback/' . $driver);

        $this->assertNull(Auth::user());
        $this->assertRedirectedTo('/login');
        $message = "User with email '%s' exists, but was not created with %s. Try to login using email and password.";
        $this->assertErrorMessage(sprintf($message, $socialiteUser->email, $driver));
    }

    public function testItShould_handleProviderCallback_oauthUserAlreadyExists()
    {
        $socialiteUser = $this->createSocialiteUser();
        $user = $this->createUser(['auth_driver' => $oldAuthDriver = uniqid()]);

        // mock provider
        $provider = $this->createMock(AbstractProvider::class);
        $provider->method('user')->willReturn($socialiteUser);

        // mock facade
        Socialite::shouldReceive('driver')
            ->with($driver = uniqid())
            ->andReturn($provider);

        $userRepository = $this->createMock(UserRepository::class);
        $this->app->instance(UserRepository::class, $userRepository);
        $userRepository->method('handleSocialiteUser')
            ->with($socialiteUser, $driver)
            ->willThrowException(new UserCreatedWithAnotherDriverException($user));

        $this->call('GET', '/login/callback/' . $driver);

        $this->assertNull(Auth::user());
        $this->assertRedirectedTo('/login');
        $message = "User with email '%s' exists, but was not created with %s. Try to login with %s.";
        $this->assertErrorMessage(sprintf($message, $socialiteUser->email, $driver, $oldAuthDriver));
    }
}
