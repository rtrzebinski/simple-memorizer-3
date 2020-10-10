<?php

namespace Tests\Integration\Http\Controllers\Web;

use App\Models\User;
use WebTestCase;

class UserRegisterWebControllerIntegrationTest extends WebTestCase
{
    /** @test */
    public function itShould_registerUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();

        $this->call(
            'POST',
            '/register',
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ]
        );

        $this->assertEquals(User::query()->first()->id, auth()->user()->id);
        $this->assertResponseRedirectedTo('/home');
    }

    /** @test */
    public function itShould_notRegisterUser_invalidInput()
    {
        $this->call('POST', '/register');

        $this->assertNull(auth()->user());
        $this->assertResponseInvalidInput();
    }
}
