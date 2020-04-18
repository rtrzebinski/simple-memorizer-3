<?php

namespace Tests\Integration\Events;

use App\Events\ExerciseCreated;

class ExerciseCreatedTest extends \TestCase
{
    /** @test */
    public function itShould_handleExerciseCreatedEvent_updatePercentOfGoodAnswersOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $lesson->subscriberPivot($user->id)->update([
            'percent_of_good_answers' => 20,
        ]);

        $this->assertEquals(20, $lesson->percentOfGoodAnswers($user->id));

        event(new ExerciseCreated($lesson, $user));

        $this->assertEquals(0, $lesson->percentOfGoodAnswers($user->id));
    }

    /** @test */
    public function itShould_handleExerciseCreatedEvent_updateExercisesCountOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->assertEquals(0, $lesson->exercises->count());
        $this->assertEquals(0, $lesson->exercises_count);

        $this->createExercise(['lesson_id' => $lesson->id]);
        event(new ExerciseCreated($lesson, $user));

        $this->assertEquals(1, $lesson->exercises()->count());
        $this->assertEquals(1, $lesson->fresh()->exercises_count);
    }
}
