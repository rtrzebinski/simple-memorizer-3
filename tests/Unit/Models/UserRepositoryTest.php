<?php

namespace Tests\Unit\Models;

use App\Models\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use TestCase;

class UserRepositoryTest extends TestCase
{
    private UserRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    /** @test */
    public function itShould_createNewUser()
    {
        $input = [
            'email' => $this->randomEmail(),
            'password' => uniqid(),
        ];

        $user = $this->repository->create($input);

        $this->assertInstanceOf(User::class, $user);
        // ensure email was stored
        $this->assertEquals($input['email'], $user->email);
        // ensure password was hashed
        $this->assertTrue(Hash::check($input['password'], $user->password));
        // ensure api_token was created
        $this->assertTrue(isset($user->api_token));
        // ensure api_token is valid
        $this->assertTrue(auth()->guard('api')->validate(['api_token' => $user->api_token]));
    }

    /** @test */
    public function itShould_findUserByCredentials()
    {
        $email = $this->randomEmail();
        $password = uniqid();

        $user = $this->createUser([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->assertEquals($user->id, $this->repository->findByCredentials($email, $password)->id);
    }

    /** @test */
    public function itShould_notFindUserByCredentials_notExistingUser()
    {
        $this->assertNull($this->repository->findByCredentials($this->randomEmail(), uniqid()));
    }

    /** @test */
    public function itShould_notFindUserByCredentials_wrongPassword()
    {
        $this->assertNull($this->repository->findByCredentials($this->createUser()->email, uniqid()));
    }
}
