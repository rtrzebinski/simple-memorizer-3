<?php

namespace Tests\Models;

class ExerciseTest extends \TestCase
{
    /** @test */
    public function itShould_defaultNumberOfAnswersToZero()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->assertEquals(0, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function test_numberOfGoodAnswersOfUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'number_of_good_answers' => 10,
        ]);

        $this->assertEquals(10, $exercise->numberOfGoodAnswersOfUser($user->id));
    }

    public function test_numberOfBadAnswersOfUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'number_of_bad_answers' => 10,
        ]);

        $this->assertEquals(10, $exercise->numberOfBadAnswersOfUser($user->id));
    }

    public function test_percentOfGoodAnswersOfUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 10,
        ]);

        $this->assertEquals(10, $exercise->percentOfGoodAnswersOfUser($user->id));
    }
}
