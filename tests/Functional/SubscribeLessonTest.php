<?php

namespace Tests\Functional;

use App\Models\User;
use Illuminate\Testing\TestResponse;
use TestCase;

class SubscribeLessonTest extends TestCase
{
    /** @test */
    public function itShould_subscribeLesson_authenticatedUser()
    {
        // create public lesson that can be accessed by anyone
        $lesson = $this->createPublicLesson();

        // create a user
        $email = $this->randomEmail();
        $password = 'test';
        $hash = '$2y$12$GrUG15bDSNGIS02sy6aineus/VkyS1whJ49jNXGCd1BNgCcvWYMTm';

        $user = $this->createUser(
            [
                'email' => $email,
                'password' => $hash,
            ]
        );

        // send login request
        /** @var TestResponse $response */
        $this->call(
            'POST',
            '/login',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        // click 'subscribe' button
        /** @var TestResponse $response */
        $response = $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        // ensure user is authenticated
        $this->assertEquals($user->id, auth()->user()->id);

        // ensure user subscribed lesson
        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);

        // ensure user is redirected to home page
        $this->assertRedirectedTo($response, "http://localhost/home");
    }

    /** @test */
    public function itShould_subscribeLesson_guestUser_signup()
    {
        // create public lesson that can be accessed by anyone
        $lesson = $this->createPublicLesson();

        // click 'subscribe' button
        /** @var TestResponse $response */
        $response = $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        // guest user - redirect to login page
        $this->assertRedirectedTo($response, "http://localhost/login");

        // send signup request
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $response = $this->call(
            'POST',
            '/register',
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ]
        );

        /** @var User $user */
        $user = User::whereEmail($email)->first();

        // ensure user is authenticated
        $this->assertEquals($user->id, auth()->user()->id);

        // ensure user subscribed lesson
        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);

        // ensure user is redirected to home page
        $this->assertRedirectedTo($response, "http://localhost/home");
    }

    /** @test */
    public function itShould_subscribeLesson_guestUser_login()
    {
        // create public lesson that can be accessed by anyone
        $lesson = $this->createPublicLesson();

        // click 'subscribe' button
        /** @var TestResponse $response */
        $response = $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        // guest user - redirect to login page
        $this->assertRedirectedTo($response, "http://localhost/login");

        // create a user
        $email = $this->randomEmail();
        $password = 'test';
        $hash = '$2y$12$GrUG15bDSNGIS02sy6aineus/VkyS1whJ49jNXGCd1BNgCcvWYMTm';
        $user = $this->createUser(
            [
                'email' => $email,
                'password' => $hash,
            ]
        );

        // send login request
        /** @var TestResponse $response */
        $response = $this->call(
            'POST',
            '/login',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        // ensure user is authenticated
        $this->assertEquals($user->id, auth()->user()->id);

        // ensure user subscribed lesson
        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);

        // ensure user is redirected to home page
        $this->assertRedirectedTo($response, "http://localhost/home");
    }

    /** @test */
    public function itShould_subscribeLesson_guestUser_login_lessonAlreadySubscribed()
    {
        // create public lesson that can be accessed by anyone
        $lesson = $this->createPublicLesson();

        // click 'subscribe' button
        /** @var TestResponse $response */
        $response = $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        // guest user - redirect to login page
        $this->assertRedirectedTo($response, "http://localhost/login");

        // create user
        $email = $this->randomEmail();
        $password = 'test';
        $hash = '$2y$12$GrUG15bDSNGIS02sy6aineus/VkyS1whJ49jNXGCd1BNgCcvWYMTm';
        $user = $this->createUser(
            [
                'email' => $email,
                'password' => $hash,
            ]
        );

        // subscribe user and ensure he can still use 'subscribe' as a guest
        $lesson->subscribe($user);

        // send login request
        /** @var TestResponse $response */
        $response = $this->call(
            'POST',
            '/login',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        // ensure user is authenticated
        $this->assertEquals($user->id, auth()->user()->id);

        // ensure user subscribed lesson
        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);

        // ensure user is redirected to home page
        $this->assertRedirectedTo($response, "http://localhost/home");
    }

    private function assertRedirectedTo(TestResponse $response, string $url)
    {
        $this->assertEquals(302, $response->baseResponse->status());
        $this->assertEquals(true, $response->baseResponse->isRedirect());
        $this->assertEquals(true, $response->baseResponse->isRedirection());
        $this->assertEquals($url, $response->baseResponse->headers->get('location'));
    }
}
