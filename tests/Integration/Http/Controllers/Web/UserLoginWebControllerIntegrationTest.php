<?php

namespace Tests\Integration\Http\Controllers\Web;

use WebTestCase;

class UserLoginWebControllerIntegrationTest extends WebTestCase
{
    /** @test */
    public function itShould_loginUser()
    {
        $email = $this->randomEmail();
        $password = 'test';
        $hash = '$2y$12$GrUG15bDSNGIS02sy6aineus/VkyS1whJ49jNXGCd1BNgCcvWYMTm';
        $user = $this->createUser(
            [
                'email' => $email,
                'password' => $hash,
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
