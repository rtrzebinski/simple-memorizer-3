<?php

namespace Tests\Http\Controllers\Web;

use Illuminate\Auth\Notifications\ResetPassword;
use Notification;

class ForgotPasswordControllerTest extends BaseTestCase
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
        $this->assertResponseRedirectedTo('/');
    }

    public function testItShould_notSendResetLinkEmail_missingEmail()
    {
        $this->call('POST', 'password/email');

        $this->assertResponseInvalidInput();
    }

    public function testItShould_notSendResetLinkEmail_invalidEmail()
    {
        $this->call('POST', 'password/email', ['email' => uniqid()]);

        $this->assertResponseInvalidInput();
    }

    public function testItShould_notSendResetLinkEmail_emailDoesNotBelongToAnyUser()
    {
        $this->call('POST', 'password/email', ['email' => $this->randomEmail()]);

        $this->assertResponseInvalidInput();
    }
}
