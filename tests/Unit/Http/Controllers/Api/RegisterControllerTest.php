<?php

namespace Tests\Unit\Http\Controllers\Api;

use ApiTestCase;
use App\Models\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;

class RegisterControllerTest extends ApiTestCase
{
    /**
     * @var UserRepository|MockObject
     */
    private $userRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->app->instance(UserRepository::class, $this->userRepositoryMock);
    }

    // register

    /** @test */
    public function itShould_registerUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $user = $this->createUser();

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with([
                'email' => $email,
                'password' => $password,
            ])
            ->willReturn($user);

        $this->callApi('POST', '/register', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseOk();
        $this->seeJsonFragment($user->makeVisible('api_token')->toArray());
    }

    /** @test */
    public function itShould_notRegisterUser_invalidInput()
    {
        $this->callApi('POST', '/register');

        $this->assertResponseInvalidInput();
    }
}
