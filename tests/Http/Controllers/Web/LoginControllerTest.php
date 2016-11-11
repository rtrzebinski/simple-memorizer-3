<?php

namespace Tests\Http\Controllers\Web;

use App\Models\User\UserRepository;
use PHPUnit_Framework_MockObject_MockObject;

class LoginControllerTest extends BaseTestCase
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

    public function testItShould_loginUser()
    {
        $email = $this->randomEmail();
        $password = $this->randomPassword();
        $user = $this->createUser([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->call('POST', '/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertEquals($user->id, auth()->user()->id);
        $this->assertRedirectedTo('/home');
    }

    public function testItShould_notLoginUser_invalidCredentials()
    {
        $this->call('POST', '/login', [
            'email' => uniqid(),
            'password' => uniqid(),
        ]);

        $this->assertNull(auth()->user());
        $this->assertInvalidInput();
    }

    public function testItShould_logoutUser()
    {
        $this->be($this->createUser());

        $this->call('POST', '/logout');

        $this->assertNull(auth()->user());
        $this->assertRedirectedTo('/');
    }
}
