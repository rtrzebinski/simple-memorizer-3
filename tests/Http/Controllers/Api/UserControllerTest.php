<?php

namespace Tests\Http\Controllers\Api;

use App\Models\User\UserRepositoryInterface;
use Illuminate\Http\Response;
use PHPUnit_Framework_MockObject_MockObject;
use TestCase;

class UserControllerTest extends TestCase
{
    /**
     * @var UserRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepositoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->userRepositoryMock = $this->getMock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepositoryMock);
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

        $this->callApi('POST', '/api/signup', [
            'email' => $email,
            'password' => $password,
        ])->seeJson($user->toArray());

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testItShould_notSignupUser_invalidInput()
    {
        $this->callApi('POST', '/api/signup');

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_loginUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $user = $this->createUser();

        $this->userRepositoryMock->expects($this->once())->method('findByCredentials')
            ->with($email, $password)->willReturn($user);

        $this->callApi('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ])->seeJson($user->toArray());

        $this->assertResponseOk();
    }

    public function testItShould_notLoginUser_invalidInput()
    {
        $this->userRepositoryMock->expects($this->never())->method('findByCredentials');

        $this->callApi('POST', '/api/login');

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notLoginUser_incorrectCredentials()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();

        $this->userRepositoryMock->expects($this->once())->method('findByCredentials')->with($email, $password);

        $this->callApi('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }
}
