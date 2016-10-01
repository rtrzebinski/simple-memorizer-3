<?php

namespace Tests\Http\Controllers\Api;

use App\Models\User\UserRepository;
use Illuminate\Http\Response;
use PHPUnit_Framework_MockObject_MockObject;
use TestCase;

class RegisterControllerTest extends TestCase
{
    /**
     * @var UserRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepositoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->app->instance(UserRepository::class, $this->userRepositoryMock);
    }

    public function testItShould_registerUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $user = $this->createUser();

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('create')->with([
                'email' => $email,
                'password' => $password,
            ])->willReturn($user);

        $this->callApi('POST', '/register', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->seeJson($user->makeVisible('api_token')->toArray());
    }

    public function testItShould_notRegisterUser_invalidInput()
    {
        $this->callApi('POST', '/register');

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
