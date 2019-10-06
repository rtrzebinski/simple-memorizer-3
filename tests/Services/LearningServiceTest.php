<?php

namespace Tests\Services;

use App\Services\LearningService;
use TestCase;
use App\Exceptions\NotEnoughExercisesException;
use App\Models\Exercise;
use App\Models\Lesson;

class LearningServiceTest extends TestCase
{
    /**
     * @var LearningService
     */
    private $learningService;

    public function setUp(): void
    {
        parent::setUp();
        $this->learningService = new LearningService();
    }

    /** @test */
    public function itShould_returnRandomExercise_exerciseOnlyHasAnswersOfAnotherUser()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 100,
        ]);

        $this->assertExerciseCanWin($lesson, $this->createUser()->id, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoPossibleExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer->id,
            'percent_of_good_answers' => 100,
        ]);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoPossibleExercises_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer->id,
            'percent_of_good_answers' => 100,
        ]);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);
        $previousExercise = $this->createExercise();

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id, $previousExercise->id);
    }

    /** @test */
    public function itShould_notReturnRandomExercise_lessonHasNoExercises()
    {
        $lesson = $this->createLesson();

        $this->expectException(NotEnoughExercisesException::class);

        $this->learningService->fetchRandomExerciseOfLesson($lesson, $this->createUser()->id);
    }

    /** @test */
    public function itShould_notReturnRandomExercise_lessonOnlyHasPreviousExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $previousExercise = $this->createExercise();

        $this->expectException(NotEnoughExercisesException::class);

        $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id, $previousExercise->id);
    }


    private function assertExerciseCanWin(Lesson $lesson, int $userId, int $exerciseId, int $previousId = null)
    {
        // 1000 retries is more than enough
        $counter = 1000;
        do {
            if (!$counter--) {
                $this->fail('Unable to fetch random exercise.');
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $userId, $previousId);
            $this->assertInstanceOf(Exercise::class, $result);
        } while ($result->id != $exerciseId && $result->id && $previousId);
    }
}
