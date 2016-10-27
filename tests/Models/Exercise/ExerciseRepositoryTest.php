<?php

namespace Tests\Models\Exercise;

use TestCase;
use App\Exceptions\NotEnoughExercisesException;
use App\Models\Exercise\Exercise;
use App\Models\Lesson\Lesson;

class ExerciseRepositoryTest extends TestCase
{
    public function testItShould_returnRandomExercise_noPossibleExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->expectException(NotEnoughExercisesException::class);

        $lesson->fetchRandomExercise($user->id);
    }

    public function testItShould_returnRandomExercise_noPossibleExercises_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $previousExercise = $this->createExercise();

        $this->expectException(NotEnoughExercisesException::class);

        $lesson->fetchRandomExercise($user->id, $previousExercise->id);
    }

    public function testItShould_returnRandomExercise_onePossibleExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $previousExercise = $this->createExercise();

        $this->assertExerciseCanWin($lesson, $user->id, $exercise->id, $previousExercise->id);
    }

    public function testItShould_returnRandomExercise_onePossibleExercise_withPrevious()
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
        $exerciseWithAnswer->handleGoodAnswer($user->id);
        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id);
    }

    public function testItShould_returnRandomExercise_twoPossibleExercises_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);
        $exerciseWithAnswer->handleGoodAnswer($user->id);
        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);
        $previousExercise = $this->createExercise();

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id, $previousExercise->id);
    }

    private function assertExerciseCanWin(Lesson $lesson, int $userId, int $exerciseId, int $previousId = null)
    {
        // 1000 retries is more then enough
        $counter = 1000;
        do {
            if (!$counter--) {
                $this->fail('Unable to fetch random exercise.');
            }
            $result = $lesson->fetchRandomExercise($userId, $previousId);
            $this->assertInstanceOf(Exercise::class, $result);
        } while ($result->id != $exerciseId && $result->id && $previousId);
    }

    public function testItShould_defaultNumberOfAnswersToZero()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->assertEquals(0, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_handleGoodAnswer()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $exercise->handleGoodAnswer($user->id);
        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(100, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_handleGoodAnswer_twice()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $exercise->handleGoodAnswer($user->id);
        $exercise->handleGoodAnswer($user->id);
        $this->assertEquals(2, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(100, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_handleBadAnswer()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $exercise->handleBadAnswer($user->id);
        $this->assertEquals(1, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_handleBadAnswer_twice()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $exercise->handleBadAnswer($user->id);
        $exercise->handleBadAnswer($user->id);
        $this->assertEquals(2, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
    }
}
