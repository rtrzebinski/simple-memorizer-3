<?php

namespace Tests\Http\Controllers\Web;

use App\Repositories\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;

class RegisterControllerTest extends BaseTestCase
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

    /** @test */
    public function itShould_registerUser()
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
        $this->assertResponseRedirectedTo('/home');
    }

    /** @test */
    public function itShould_notRegisterUser_invalidInput()
    {
        $this->userRepositoryMock
            ->expects($this->never())
            ->method('create');

        $this->call('POST', '/register');

        $this->assertNull(auth()->user());
        $this->assertResponseInvalidInput();
    }
}
