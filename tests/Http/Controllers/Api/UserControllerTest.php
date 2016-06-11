<?php

namespace Tests\Http\Controllers\Api;

use App\Repositories\UserRepository;
use Illuminate\Http\Response;
use TestCase;

class UserControllerTest extends TestCase
{
    private $userRepositoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->userRepositoryMock = $this->getMock(UserRepository::class);
        $this->app->instance(UserRepository::class, $this->userRepositoryMock);
    }

    public function testItShould_signupUser()
    {
        $email = $this->randomEmail();
        $password = uniqid();

        $this->userRepositoryMock->expects($this->once())->method('create')->with([
            'email' => $email,
            'password' => $password,
        ]);

        $this->json('POST', '/api/signup', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testItShould_notSignupUser_invalidInput()
    {
        $email = uniqid();
        $password = uniqid();

        $this->json('POST', '/api/signup', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_loginUser()
    {
        $email = uniqid();
        $password = uniqid();
        $user = $this->makeUser();

        $this->userRepositoryMock->expects($this->once())->method('findByCredentials')
            ->with($email, $password)->willReturn($user);

        $this->json('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ])->seeJson(['token' => $user->api_token]);

        $this->assertResponseOk();
    }

    public function testItShould_notLoginUser_incorrectCredentials()
    {
        $email = uniqid();
        $password = uniqid();

        $this->userRepositoryMock->expects($this->once())->method('findByCredentials')
            ->with($email, $password);

        $this->json('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }
}
