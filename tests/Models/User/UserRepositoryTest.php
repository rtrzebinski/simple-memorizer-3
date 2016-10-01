<?php

namespace Tests\Models\Users;

use App\Models\User\UserRepository;
use App\Models\User\User;
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
        // ensure email was stored
        $this->assertEquals($input['email'], $user->email);
        // ensure password was hashed
        $this->assertTrue(Hash::check($input['password'], $user->password));
        // ensure api_token was created
        $this->assertTrue(isset($user->api_token));
        // ensure api_token is valid
        $this->assertTrue(auth()->guard('api')->validate(['api_token' => $user->api_token]));
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
