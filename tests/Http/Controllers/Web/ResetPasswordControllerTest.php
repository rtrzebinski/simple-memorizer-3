<?php

namespace Tests\Http\Controllers\Web;

use Carbon\Carbon;
use DB;

class ResetPasswordControllerTest extends BaseTestCase
{
    public function testItShould_showResetForm()
    {
        $this->call('GET', 'password/reset/' . uniqid());

        $this->assertResponseOk();
        $this->see('Reset Password');
    }

    public function testItShould_resetPassword()
    {
        $user = $this->createUser();
        $password = $this->randomPassword();
        $token = uniqid();

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => bcrypt($token),
            'created_at' => Carbon::now(),
        ]);

        $parameters = [
            'token' => $token,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $this->call('POST', 'password/reset', $parameters);

        $this->assertSessionHas('status', 'Your password has been reset!');
        $this->assertResponseRedirectedTo('/home');
    }

    public function testItShould_notResetPassword_invalidToken()
    {
        $user = $this->createUser();
        $password = $this->randomPassword();
        $token = uniqid();

        $parameters = [
            'token' => $token,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $this->call('POST', 'password/reset', $parameters);

        $this->assertResponseInvalidInput();
    }

    public function testItShould_notResetPassword_invalidPassword()
    {
        $user = $this->createUser();
        $token = uniqid();

        $parameters = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'a',
            'password_confirmation' => 'a',
        ];

        $this->call('POST', 'password/reset', $parameters);

        $this->assertResponseInvalidInput();
    }
}
