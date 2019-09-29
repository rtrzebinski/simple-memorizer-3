<?php

use App\Models\Exercise;
use App\Models\ExerciseResult;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Socialite\Two\User as SocialiteUser;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    use DatabaseMigrations;

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
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Random, valid url.
     * @return string
     */
    public function randomUrl(): string
    {
        return 'http://'.uniqid().'.example.com';
    }

    /**
     * Random valid email address.
     * @return string
     */
    protected function randomEmail(): string
    {
        return uniqid().'@example.com';
    }

    /**
     * Random valid password.
     * @return string
     */
    protected function randomPassword(): string
    {
        return uniqid();
    }

    /**
     * Last created instance of the class.
     * @param $class
     * @return mixed
     */
    protected function last($class): Model
    {
        return app($class)->orderBy('id', 'desc')->first();
    }

    /**
     * @param int $lessonId
     */
    protected function createExercisesRequiredToLearnLesson(int $lessonId)
    {
        $minExercisesToLearnLesson = config('app.min_exercises_to_learn_lesson');
        for ($i = $minExercisesToLearnLesson; $i > 0; $i--) {
            $this->createExercise(['lesson_id' => $lessonId]);
        }
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
     * @return SocialiteUser
     */
    protected function createSocialiteUser(): SocialiteUser
    {
        $user = new SocialiteUser();
        $user->email = $this->randomEmail();
        return $user;
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

    /**
     * @param array $data
     * @return ExerciseResult
     */
    protected function createExerciseResult(array $data = [])
    {
        return factory(ExerciseResult::class)->create($data);
    }

    /**
     * @param User|null $user
     * @return Lesson
     */
    protected function createPublicLesson(User $user = null): Lesson
    {
        $attributes = ['visibility' => 'public'];

        if ($user) {
            $attributes['owner_id'] = $user->id;
        }

        $lesson = $this->createLesson($attributes);

        if ($user) {
            // when lesson is created by user it's also subscribed
            // so let's replicate this here to make tests more reliable
            $lesson->subscribe($user);
        }

        return $lesson;
    }

    /**
     * @param User|null $user
     * @return Lesson
     */
    protected function createPrivateLesson(User $user = null): Lesson
    {
        $attributes = ['visibility' => 'private'];

        if ($user) {
            $attributes['owner_id'] = $user->id;
        }

        $lesson = $this->createLesson($attributes);

        if ($user) {
            // when lesson is created by user it's also subscribed
            // so let's replicate this here to make tests more reliable
            $lesson->subscribe($user);
        }

        return $lesson;
    }
}
