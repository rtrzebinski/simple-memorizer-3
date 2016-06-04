<?php

use App\User;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Random valid email address.
     * @return string
     */
    protected function randomEmail() : string
    {
        return uniqid() . '@example.com';
    }

    /**
     * Make object without saving to db.
     * @return User
     */
    protected function makeUser()
    {
        return factory(User::class)->make();
    }

    /**
     * Create object and save to db.
     * @param array $data
     * @return mixed
     */
    protected function createUser(array $data = [])
    {
        return factory(User::class)->create($data);
    }
}
