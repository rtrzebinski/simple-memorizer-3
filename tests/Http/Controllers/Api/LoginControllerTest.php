<?php

namespace Tests\Http\Controllers\Api;

use App\Repositories\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;

class LoginControllerTest extends BaseTestCase
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

    // login

    public function testItShould_loginUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $user = $this->createUser();

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByCredentials')
            ->with($email, $password)
            ->willReturn($user);

        $this->callApi('POST', '/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseOk();
        $this->seeJsonFragment($user->makeVisible('api_token')->toArray());
    }

    public function testItShould_notLoginUser_invalidInput()
    {
        $this->userRepositoryMock
            ->expects($this->never())
            ->method('findByCredentials');

        $this->callApi('POST', '/login');

        $this->assertResponseInvalidInput();
    }

    public function testItShould_notLoginUser_incorrectCredentials()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByCredentials')
            ->with($email, $password);

        $this->callApi('POST', '/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseUnauthorised();
    }
}
