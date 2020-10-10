<?php

namespace Tests\Integration\Http\Controllers\Api;

use ApiTestCase;

class UserLoginApiControllerIntegrationTest extends ApiTestCase
{
    // login

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

        $this->callApi(
            'POST',
            '/login',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        $this->assertResponseOk();
        $this->seeJsonFragment($user->makeVisible('api_token')->toArray());
    }

    /** @test */
    public function itShould_notLoginUser_invalidInput()
    {
        $this->callApi('POST', '/login');

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notLoginUser_incorrectCredentials()
    {
        $this->callApi(
            'POST',
            '/login',
            [
                'email' => $this->randomEmail(),
                'password' => uniqid(),
            ]
        );

        $this->assertResponseUnauthorised();
    }
}
