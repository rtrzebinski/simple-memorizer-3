<?php

namespace Tests\Http\Controllers\Api;

use Illuminate\Auth\Notifications\ResetPassword;
use Notification;

class ForgotPasswordControllerTest extends BaseTestCase
{
    // sendResetLinkEmail

    public function testItShould_sendResetLinkEmail()
    {
        $user = $this->createUser()->fresh();

        Notification::fake();

        $this->callApi('POST', '/password/email', [
            'email' => $user->email,
        ]);

        $this->assertResponseOk();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function testItShould_notSendResetLinkEmail_missingEmail()
    {
        $this->callApi('POST', '/password/email');

        $this->assertInvalidInput();
    }

    public function testItShould_notSendResetLinkEmail_invalidEmail()
    {
        $this->callApi('POST', '/password/email', ['email' => uniqid()]);

        $this->assertInvalidInput();
    }

    public function testItShould_notSendResetLinkEmail_emailDoesNotBelongToAnyUser()
    {
        $this->callApi('POST', '/password/email', ['email' => $this->randomEmail()]);

        $this->assertResponseStatus(450);
    }
}
