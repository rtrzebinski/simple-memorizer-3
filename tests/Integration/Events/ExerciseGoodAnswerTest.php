<?php

namespace Tests\Integration\Events;

use App\Events\ExerciseGoodAnswer;

class ExerciseGoodAnswerTest extends \TestCase
{
    /** @test */
    public function itShould_handleExerciseGoodAnswerEvent_updateNumberOfGoodAnswersOfExercise()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->assertEquals(0, $exercise->numberOfGoodAnswersOfUser($user->id));

        event(new ExerciseGoodAnswer($exercise->id, $user));

        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
    }
}
