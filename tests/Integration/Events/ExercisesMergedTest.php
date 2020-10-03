<?php

namespace Tests\Integration\Events;

use App\Events\ExercisesMerged;

class ExercisesMergedTest extends \TestCase
{
    /** @test */
    public function itShould_handleExerciseMergedEvent_updatePercentOfGoodAnswersOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->subscriberPivot($lesson, $user->id)->update(
            [
                'percent_of_good_answers' => 20,
            ]
        );

        $this->assertEquals(20, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));

        event(new ExercisesMerged($lesson, $user));

        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_handleExerciseMergedEvent_updateExercisesCountOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->assertEquals(0, $lesson->exercises->count());
        $this->assertEquals(0, $lesson->exercises_count);

        $this->createExercise(['lesson_id' => $lesson->id]);
        event(new ExercisesMerged($lesson, $user));

        $this->assertEquals(1, $lesson->exercises()->count());
        $this->assertEquals(1, $lesson->fresh()->exercises_count);
    }
}
