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

    public function setUp()
    {
        parent::setUp();
        $this->learningService = new LearningService();
    }

    // not
    public function testItShould_notReturnRandomExercise_lessonHasNoExercises()
    {
        $lesson = $this->createLesson();

        $this->expectException(NotEnoughExercisesException::class);

        $this->learningService->fetchRandomExerciseOfLesson($lesson, $this->createUser()->id);
    }

    // not
    public function testItShould_notReturnRandomExercise_lessonOnlyHasPreviousExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $previousExercise = $this->createExercise();

        $this->expectException(NotEnoughExercisesException::class);

        $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id, $previousExercise->id);
    }

    public function testItShould_returnRandomExercise_exerciseOnlyHasAnswersOfAnotherUser()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->learningService->handleBadAnswer($exercise->id, $this->createUser()->id);

        $this->assertExerciseCanWin($lesson, $this->createUser()->id, $exercise->id);
    }

    public function testItShould_returnRandomExercise_onePossibleExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exercise->id);
    }

    public function testItShould_returnRandomExercise_twoPossibleExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->learningService->handleBadAnswer($exerciseWithAnswer->id, $user->id);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id);
    }

    public function testItShould_returnRandomExercise_twoPossibleExercises_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->learningService->handleGoodAnswer($exerciseWithAnswer->id, $user->id);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);
        $previousExercise = $this->createExercise();

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id, $previousExercise->id);
    }



//    public function testItShould_defaultNumberOfAnswersToZero()
//    {
//        $user = $this->createUser();
//        $exercise = $this->createExercise();
//
//        $this->assertEquals(0, $exercise->numberOfGoodAnswersOfUser($user->id));
//        $this->assertEquals(0, $exercise->numberOfBadAnswersOfUser($user->id));
//        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
//    }

    public function testItShould_handleGoodAnswer()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->learningService->handleGoodAnswer($exercise->id, $user->id);

        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(100, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_handleGoodAnswer_twice()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->learningService->handleGoodAnswer($exercise->id, $user->id);
        $this->learningService->handleGoodAnswer($exercise->id, $user->id);

        $this->assertEquals(2, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(100, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_handleBadAnswer()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->learningService->handleBadAnswer($exercise->id, $user->id);

        $this->assertEquals(1, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_handleBadAnswer_twice()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->learningService->handleBadAnswer($exercise->id, $user->id);
        $this->learningService->handleBadAnswer($exercise->id, $user->id);

        $this->assertEquals(2, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
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
