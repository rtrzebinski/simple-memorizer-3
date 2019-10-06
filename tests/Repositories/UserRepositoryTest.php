<?php

namespace Tests\Models\Repositories;

use App\Exceptions\UserCreatedWithAnotherDriverException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use TestCase;

class UserRepositoryTest extends TestCase
{
    /**
     * @var UserRepository
     */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    /** @test */
    public function itShould_createNewUser_withPassword()
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
    public function itShould_createNewUser_withAuthDriver()
    {
        $input = [
            'email' => $this->randomEmail(),
            'password' => uniqid(),
        ];
        $driver = uniqid();

        $user = $this->repository->create($input, $driver);

        $this->assertInstanceOf(User::class, $user);
        // ensure email was stored
        $this->assertEquals($input['email'], $user->email);
        // ensure password was hashed
        $this->assertFalse(Hash::check($input['password'], $user->password));
        // ensure api_token was created
        $this->assertTrue(isset($user->api_token));
        // ensure api_token is valid
        $this->assertTrue(auth()->guard('api')->validate(['api_token' => $user->api_token]));
        // ensure auth_driver was set
        $this->assertEquals($driver, $user->auth_driver);
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

    /** @test */
    public function itShould_handleSocialiteUser_userExists_sameDriver()
    {
        $driver = uniqid();
        $socialiteUser = $this->createSocialiteUser();
        $user = $this->createUser([
            'email' => $socialiteUser->email,
            'auth_driver' => $driver,
        ]);

        $result = $this->repository->handleSocialiteUser($socialiteUser, $driver);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function itShould_handleSocialiteUser_userExists_differentDriver()
    {
        $driver = uniqid();
        $socialiteUser = $this->createSocialiteUser();
        $user = $this->createUser([
            'email' => $socialiteUser->email,
            'auth_driver' => $oldDriver = uniqid(),
        ])->fresh();

        try {
            $this->repository->handleSocialiteUser($socialiteUser, $driver);
        } catch (UserCreatedWithAnotherDriverException $e) {
            $this->assertEquals($e->user, $user);
            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    public function itShould_handleSocialiteUser_userDoesNotExist()
    {
        $driver = uniqid();
        $socialiteUser = $this->createSocialiteUser();

        $result = $this->repository->handleSocialiteUser($socialiteUser, $driver);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($driver, $result->auth_driver);
        $this->assertEquals($socialiteUser->email, $result->email);
    }
}
