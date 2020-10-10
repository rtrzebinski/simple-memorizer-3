<?php

namespace Tests\Integration\Http\Controllers\Web;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use WebTestCase;

class ResetPasswordWebControllerIntegrationTest extends WebTestCase
{
    /** @test */
    public function itShould_showResetForm()
    {
        $this->call('GET', 'password/reset/' . uniqid());

        $this->assertResponseOk();
        $this->see('Reset Password');
    }

    /** @test */
    public function itShould_resetPassword()
    {
        $user = $this->createUser();
        $password = $this->randomPassword();
        $token = 'test';
        $hash = '$2y$12$GrUG15bDSNGIS02sy6aineus/VkyS1whJ49jNXGCd1BNgCcvWYMTm';

        DB::table('password_resets')->insert(
            [
                'email' => $user->email,
                'token' => $hash,
                'created_at' => Carbon::now(),
            ]
        );

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

    /** @test */
    public function itShould_notResetPassword_invalidToken()
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

    /** @test */
    public function itShould_notResetPassword_invalidPassword()
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
