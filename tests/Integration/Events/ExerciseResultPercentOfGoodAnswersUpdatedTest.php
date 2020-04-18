<?php

namespace Tests\Integration\Events;

use App\Events\ExerciseResultPercentOfGoodAnswersUpdated;

class ExerciseResultPercentOfGoodAnswersUpdatedTest extends \TestCase
{
    /** @test */
    public function itShould_handleExerciseResultPercentOfGoodAnswersUpdatedEvent_updatePercentOfGoodAnswersOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $lesson->subscriberPivot($user->id)->update([
            'percent_of_good_answers' => 20,
        ]);

        $this->assertEquals(20, $lesson->percentOfGoodAnswers($user->id));

        event(new ExerciseResultPercentOfGoodAnswersUpdated($exercise->id, $user));

        $this->assertEquals(0, $lesson->percentOfGoodAnswers($user->id));
    }
}
