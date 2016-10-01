<?php

namespace Tests\Http\Controllers\Auth;

use App\Models\User\UserRepository;
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
                'password_confirmation' => $password,
            ])->willReturn($user);

        $this->call('POST', '/register', [
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $this->assertEquals($user->id, auth()->user()->id);
        $this->assertRedirectedTo('/home');
    }

    public function testItShould_notRegisterUser_invalidInput()
    {
        $referer = $this->randomUrl();

        $this->userRepositoryMock
            ->expects($this->never())
            ->method('create');

        $this->call('POST', '/register', $parameters = [], $cookies = [], $files = [], ['HTTP_REFERER' => $referer]);

        $this->assertNull(auth()->user());
        $this->assertRedirectedTo($referer);
        $this->assertSessionHasErrors();
    }
}
