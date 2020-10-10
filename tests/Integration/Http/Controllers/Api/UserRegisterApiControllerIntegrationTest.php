<?php

namespace Tests\Integration\Http\Controllers\Api;

use ApiTestCase;

class UserRegisterApiControllerIntegrationTest extends ApiTestCase
{
    // register

    /** @test */
    public function itShould_registerUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $this->createUser();

        $this->callApi(
            'POST',
            '/register',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        $this->assertResponseOk();
    }

    /** @test */
    public function itShould_notRegisterUser_userAlreadyExist()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $this->createUser(
            [
                'email' => $email,
            ]
        );

        $this->callApi(
            'POST',
            '/register',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        // 422 with "The email has already been taken."
        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notRegisterUser_invalidInput()
    {
        $this->callApi('POST', '/register');

        $this->assertResponseInvalidInput();
    }
}
