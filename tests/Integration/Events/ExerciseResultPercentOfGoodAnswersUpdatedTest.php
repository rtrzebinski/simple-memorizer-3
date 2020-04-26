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

        $this->subscriberPivot($lesson, $user->id)->update([
            'percent_of_good_answers' => 20,
        ]);

        $this->assertEquals(20, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));

        event(new ExerciseResultPercentOfGoodAnswersUpdated($exercise->id, $user));

        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }
}
