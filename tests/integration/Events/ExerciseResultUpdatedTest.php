<?php

namespace Tests\Integration\Events;

use App\Events\ExerciseResultUpdated;

class ExerciseResultUpdatedTest extends \TestCase
{
    /** @test */
    public function itShould_handleExerciseResultUpdatedEvent_updatePercentOfGoodAnswersOfExerciseResult()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $exerciseResult = $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 20,
        ]);

        $this->assertEquals(20, $exerciseResult->percent_of_good_answers);

        event(new ExerciseResultUpdated($exercise->id, $user));

        $this->assertEquals(0, $exerciseResult->fresh()->percent_of_good_answers);
    }
}
