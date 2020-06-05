<?php

use App\Models\Exercise;
use App\Models\ExerciseResult;
use App\Models\Lesson;
use App\Models\User;
use App\Structures\UserExercise\UserExercise;
use App\Structures\UserLesson\UserLesson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    use DatabaseMigrations;

    /**
     * The base URL to use while testing the application.
     */
    protected string $baseUrl = 'http://localhost';

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

        /** @var Lesson $lesson */
        $lesson = Lesson::find($lessonId);
        $lesson->exercises_count = $minExercisesToLearnLesson;
        $lesson->save();

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

    /**
     * @param User     $user
     * @param Exercise $exercise
     * @return UserExercise
     */
    protected function createUserExercise(User $user, Exercise $exercise): UserExercise
    {
        $userExercise = new UserExercise();
        $userExercise->exercise_id = $exercise->id;
        $userExercise->lesson_id = $exercise->lesson_id;
        $userExercise->question = $exercise->question;
        $userExercise->answer = $exercise->answer;

        /** @var ExerciseResult $exerciseResult */
        $exerciseResult = $exercise->results()->where('user_id', $user->id)->first();

        if ($exerciseResult) {
            $userExercise->number_of_good_answers = $exerciseResult->number_of_good_answers;
            $userExercise->number_of_good_answers_today = $exerciseResult->number_of_good_answers_today;
            $userExercise->latest_good_answer = $exerciseResult->latest_good_answer;
            $userExercise->number_of_bad_answers = $exerciseResult->number_of_bad_answers;
            $userExercise->number_of_bad_answers_today = $exerciseResult->number_of_bad_answers_today;
            $userExercise->latest_bad_answer = $exerciseResult->latest_bad_answer;
            $userExercise->percent_of_good_answers = $exerciseResult->percent_of_good_answers;
        } else {
            $userExercise->number_of_good_answers = 0;
            $userExercise->number_of_good_answers_today = 0;
            $userExercise->latest_good_answer = null;
            $userExercise->number_of_bad_answers = 0;
            $userExercise->number_of_bad_answers_today = 0;
            $userExercise->latest_bad_answer = null;
            $userExercise->percent_of_good_answers = 0;
        }

        return $userExercise;
    }

    protected function createUserLesson(User $user, Lesson $lesson, $isBidirectional)
    {
        $userLesson = new UserLesson();
        $userLesson->lesson_id = $lesson->id;
        $userLesson->owner_id = $user->id;
        $userLesson->name = $lesson->name;
        $userLesson->is_bidirectional = $isBidirectional;
        return $userLesson;
    }

    /**
     * @param Exercise $exercise
     * @param int      $userId
     * @return int
     */
    protected function numberOfGoodAnswers(Exercise $exercise, int $userId): int
    {
        $exerciseResult = $exercise->results()->where('exercise_results.user_id', $userId)->first();
        return $exerciseResult ? $exerciseResult->number_of_good_answers : 0;
    }

    /**
     * @param Exercise $exercise
     * @param int      $userId
     * @return int
     */
    protected function numberOfBadAnswers(Exercise $exercise, int $userId): int
    {
        $exerciseResult = $exercise->results()->where('exercise_results.user_id', $userId)->first();
        return $exerciseResult ? $exerciseResult->number_of_bad_answers : 0;
    }

    /**
     * @param Exercise $exercise
     * @param int      $userId
     * @return int
     */
    protected function percentOfGoodAnswersOfExercise(Exercise $exercise, int $userId): int
    {
        $exerciseResult = $exercise->results()->where('exercise_results.user_id', $userId)->first();
        return $exerciseResult ? $exerciseResult->percent_of_good_answers : 0;
    }

    /**
     * @param Lesson $lesson
     * @param int    $userId
     * @return int
     * @throws Exception
     */
    protected function percentOfGoodAnswersOfLesson(Lesson $lesson, int $userId): int
    {
        if ($pivot = $this->subscriberPivot($lesson, $userId)) {
            return $pivot->percent_of_good_answers;
        }

        throw new \Exception('User does not subscribe lesson: '.$lesson->id);
    }

    /**
     * @param Lesson $lesson
     * @param int    $userId
     * @return bool
     * @throws Exception
     */
    protected function isBidirectional(Lesson $lesson, int $userId): bool
    {
        if ($pivot = $this->subscriberPivot($lesson, $userId)) {
            return $pivot->bidirectional;
        }

        throw new \Exception('User does not subscribe lesson: '.$lesson->id);
    }

    /**
     * @param Lesson $lesson
     * @param int    $userId
     * @param bool   $bidirectional
     */
    protected function updateBidirectional(Lesson $lesson, int $userId, bool $bidirectional)
    {
        $subscriberPivot = $this->subscriberPivot($lesson, $userId);
        $subscriberPivot->bidirectional = $bidirectional;
        $subscriberPivot->save();
    }

    /**
     * @param Lesson $lesson
     * @param int    $userId
     * @return bool
     * @throws Exception
     */
    protected function isFavourite(Lesson $lesson, int $userId): bool
    {
        if ($pivot = $this->subscriberPivot($lesson, $userId)) {
            return $pivot->favourite;
        }

        throw new \Exception('User does not subscribe lesson: '.$lesson->id);
    }

    /**
     * @param Lesson $lesson
     * @param int    $userId
     * @param bool   $favourite
     */
    protected function updateFavourite(Lesson $lesson, int $userId, bool $favourite)
    {
        $subscriberPivot = $this->subscriberPivot($lesson, $userId);
        $subscriberPivot->favourite = $favourite;
        $subscriberPivot->save();
    }

    /**
     * @param Lesson $lesson
     * @param int    $userId
     * @return Pivot|null
     */
    protected function subscriberPivot(Lesson $lesson, int $userId): ?Pivot
    {
        $user = $lesson->subscribedUsers()->where('user_id', $userId)->first();

        if ($user instanceof User) {
            return $user->pivot;
        }

        return null;
    }

    public function trueFalseProvider()
    {
        return [
            [true],
            [false],
        ];
    }
}
