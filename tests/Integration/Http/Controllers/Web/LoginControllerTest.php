<?php

namespace Tests\Integration\Http\Controllers\Web;

use WebTestCase;

class LoginControllerTest extends WebTestCase
{
    /** @test */
    public function itShould_loginUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $user = $this->createUser(
            [
                'email' => $email,
                'password' => bcrypt($password),
            ]
        );

        $this->call(
            'POST',
            '/login',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        $this->assertEquals($user->id, auth()->user()->id);
        $this->assertResponseRedirectedTo('/home');
    }

    /** @test */
    public function itShould_notLoginUser_invalidCredentials()
    {
        $this->call(
            'POST',
            '/login',
            [
                'email' => uniqid(),
                'password' => uniqid(),
            ]
        );

        $this->assertNull(auth()->user());
        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_logoutUser()
    {
        $this->be($this->createUser());

        $this->call('POST', '/logout');

        $this->assertNull(auth()->user());
        $this->assertResponseRedirectedTo('/');
    }
}
