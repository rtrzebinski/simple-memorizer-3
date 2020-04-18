<?php

namespace Tests\Unit\Http\Controllers\Api;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

class ForgotPasswordControllerTest extends TestCase
{
    // sendResetLinkEmail

    /** @test */
    public function itShould_sendResetLinkEmail()
    {
        $user = $this->createUser()->fresh();

        Notification::fake();

        $this->callApi('POST', '/password/email', [
            'email' => $user->email,
        ]);

        $this->assertResponseOk();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    /** @test */
    public function itShould_notSendResetLinkEmail_missingEmail()
    {
        $this->callApi('POST', '/password/email');

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notSendResetLinkEmail_invalidEmail()
    {
        $this->callApi('POST', '/password/email', ['email' => uniqid()]);

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notSendResetLinkEmail_emailDoesNotBelongToAnyUser()
    {
        $this->callApi('POST', '/password/email', ['email' => $this->randomEmail()]);

        $this->assertResponseStatus(450);
    }
}
