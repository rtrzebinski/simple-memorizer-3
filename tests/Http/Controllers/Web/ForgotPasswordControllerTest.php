<?php

namespace Tests\Http\Controllers\Web;

use Illuminate\Auth\Notifications\ResetPassword;
use Notification;
use TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    public function testItShould_showLinkRequestForm()
    {
        $this->call('GET', 'password/reset');

        $this->assertResponseOk();
        $this->see('Reset Password');
    }

    public function testItShould_sendResetLinkEmail()
    {
        $user = $this->createUser()->fresh();

        Notification::fake();

        $this->call('POST', 'password/email', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertSessionHas('status', 'We have e-mailed your password reset link!');
        $this->assertRedirectedTo('/');
    }

    public function testItShould_notSendResetLinkEmail_missingEmail()
    {
        $referer = $this->randomUrl();

        $this->call('POST', 'password/email', $parameters = [], $cookies = [], $files = [],
            ['HTTP_REFERER' => $referer]);

        $this->assertRedirectedTo($referer);
        $this->assertSessionHasErrors();
    }

    public function testItShould_notSendResetLinkEmail_invalidEmail()
    {
        $referer = $this->randomUrl();

        $this->call('POST', 'password/email', ['email' => uniqid()], $cookies = [], $files = [],
            ['HTTP_REFERER' => $referer]);

        $this->assertRedirectedTo($referer);
        $this->assertSessionHasErrors();
    }

    public function testItShould_notSendResetLinkEmail_emailDoesNotBelongToAnyUser()
    {
        $referer = $this->randomUrl();

        $this->call('POST', 'password/email', ['email' => $this->randomEmail()], $cookies = [], $files = [],
            ['HTTP_REFERER' => $referer]);

        $this->assertRedirectedTo($referer);
        $this->assertSessionHasErrors();
    }
}
