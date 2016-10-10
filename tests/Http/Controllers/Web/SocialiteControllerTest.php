<?php

namespace Tests\Http\Controllers\Web;

use App\Models\User\UserRepository;
use Auth;
use Laravel\Socialite\Two\AbstractProvider;
use Socialite;
use Illuminate\Http\RedirectResponse;
use TestCase;

class SocialiteControllerTest extends TestCase
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
}
