<?php

namespace Tests\Unit\Http\Controllers\Web;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use WebTestCase;

class ForgotPasswordControllerTest extends WebTestCase
{
    /** @test */
    public function itShould_showLinkRequestForm()
    {
        $this->call('GET', 'password/reset');

        $this->assertResponseOk();
        $this->see('Reset Password');
    }

    /** @test */
    public function itShould_sendResetLinkEmail()
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

    /** @test */
    public function itShould_notSendResetLinkEmail_missingEmail()
    {
        $this->call('POST', 'password/email');

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notSendResetLinkEmail_invalidEmail()
    {
        $this->call('POST', 'password/email', ['email' => uniqid()]);

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notSendResetLinkEmail_emailDoesNotBelongToAnyUser()
    {
        $this->call('POST', 'password/email', ['email' => $this->randomEmail()]);

        $this->assertResponseInvalidInput();
    }
}
