<?php

namespace Tests\Integration\Events;

use App\Events\ExerciseDeleted;

class ExerciseDeletedTest extends \TestCase
{
    /** @test */
    public function itShould_handleExerciseDeletedEvent_updatePercentOfGoodAnswersOfLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();
        $lesson->subscribe($user);

        $this->subscriberPivot($lesson, $user->id)->update([
            'percent_of_good_answers' => 20,
        ]);

        $this->assertEquals(20, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));

        event(new ExerciseDeleted($lesson, $user));

        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_handleExerciseDeletedEvent_updateExercisesCountOfLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $lesson->exercises_count = 1;
        $lesson->update();

        $this->assertEquals(1, $lesson->exercises()->count());
        $this->assertEquals(1, $lesson->fresh()->exercises_count);

        $exercise->delete();
        event(new ExerciseDeleted($lesson, $user));

        $this->assertEquals(0, $lesson->exercises->count());
        $this->assertEquals(0, $lesson->exercises_count);
    }
}
