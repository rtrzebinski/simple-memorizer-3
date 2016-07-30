<?php

use App\Models\Exercise\Exercise;
use App\Models\Lesson\Lesson;
use App\Models\User\User;

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
     * Call api route as guest, or authenticated user.
     * @param $method
     * @param $uri
     * @param array $data
     * @param User $user
     * @return $this
     */
    protected function callApi($method, $uri, array $data = [], User $user = null)
    {
        $headers = [];
        if ($user instanceof User) {
            $headers = ['Authorization' => 'Bearer ' . $user->api_token];
        }
        return parent::json($method, $uri, $data, $headers);
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
     * Random valid password.
     * @return string
     */
    protected function randomPassword() : string
    {
        return uniqid();
    }

    /**
     * @param array $data
     * @return User
     */
    protected function createUser(array $data = [])
    {
        return factory(User::class)->create($data);
    }

    /**
     * @param array $data
     * @return Exercise
     */
    protected function createExercise(array $data = [])
    {
        return factory(Exercise::class)->create($data);
    }

    /**
     * @param array $data
     * @return Lesson
     */
    protected function createLesson(array $data = [])
    {
        return factory(Lesson::class)->create($data);
    }
}
