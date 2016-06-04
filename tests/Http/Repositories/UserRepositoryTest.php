<?php

namespace Tests\Http\Repositories;

use App\Repositories\UserRepository;
use App\User;
use Hash;
use TestCase;

class UserRepositoryTest extends TestCase
{
    /**
     * @var UserRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    public function testItShould_createNewUser()
    {
        $input = [
            'email' => $this->randomEmail(),
            'password' => uniqid(),
        ];

        $user = $this->repository->create($input);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($input['email'], $user->email);
        $this->assertTrue(Hash::check($input['password'], $user->password));
        // repository should create api_token
        $this->assertTrue(strlen($user->api_token) > 0);
    }

    public function testItShould_findUserByCredentials()
    {
        $email = $this->randomEmail();
        $password = uniqid();

        $user = $this->createUser([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->assertEquals($user->id, $this->repository->findByCredentials($email, $password)->id);
    }

    public function testItShould_notFindUserByCredentials_notExistingUser()
    {
        $this->assertNull($this->repository->findByCredentials($this->randomEmail(), uniqid()));
    }

    public function testItShould_notFindUserByCredentials_wrongPassword()
    {
        $this->assertNull($this->repository->findByCredentials($this->createUser()->email, uniqid()));
    }
}
