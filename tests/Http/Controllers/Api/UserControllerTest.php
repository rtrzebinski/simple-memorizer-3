<?php

namespace Tests\Http\Controllers\Api;

use App\Models\User\UserRepository;
use Illuminate\Http\Response;
use PHPUnit_Framework_MockObject_MockObject;
use TestCase;

class UserControllerTest extends TestCase
{
    /**
     * @var UserRepository|PHPUnit_Framework_MockObject_MockObject
     */
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
        $password = $this->randomPassword();
        $user = $this->createUser();

        $this->userRepositoryMock->expects($this->once())->method('create')->with([
            'email' => $email,
            'password' => $password,
        ])->willReturn($user);

        $this->json('POST', '/api/signup', [
            'email' => $email,
            'password' => $password,
        ])->seeJson($user->toArray());

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testItShould_notSignupUser_invalidInput()
    {
        $this->json('POST', '/api/signup');

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_loginUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $user = $this->createUser();

        $this->userRepositoryMock->expects($this->once())->method('findByCredentials')
            ->with($email, $password)->willReturn($user);

        $this->json('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ])->seeJson($user->toArray());

        $this->assertResponseOk();
    }

    public function testItShould_notLoginUser_invalidInput()
    {
        $this->userRepositoryMock->expects($this->never())->method('findByCredentials');

        $this->json('POST', '/api/login');

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notLoginUser_incorrectCredentials()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();

        $this->userRepositoryMock->expects($this->once())->method('findByCredentials')
            ->with($email, $password);

        $this->json('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }
}
