<?php

namespace Tests\Integration\Events;

use App\Events\ExerciseBadAnswer;

class ExerciseBadAnswerTest extends \TestCase
{
    /** @test */
    public function itShould_handleExerciseBadAnswerEvent_updateNumberOfBadAnswersOfExercise()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->assertEquals(0, $this->numberOfBadAnswers($exercise, $user->id));

        event(new ExerciseBadAnswer($exercise->id, $user));

        $this->assertEquals(1, $this->numberOfBadAnswers($exercise, $user->id));
    }
}
