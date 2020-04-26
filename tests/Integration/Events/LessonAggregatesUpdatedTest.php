<?php

namespace Tests\Integration\Events;

use App\Events\LessonAggregatesUpdated;

class LessonAggregatesUpdatedTest extends \TestCase
{
    /** @test */
    public function itShould_handleLessonAggregatesUpdatedEvent_updatePercentOfGoodAnswersOfLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();
        $lesson->subscribe($user);

        $this->subscriberPivot($lesson, $user->id)->update([
            'percent_of_good_answers' => 20,
        ]);

        $this->assertEquals(20, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));

        event(new LessonAggregatesUpdated($lesson, $user));

        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_handleLessonAggregatesUpdatedEvent_updateExercisesCountOfLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();
        $lesson->subscribe($user);

        $this->assertEquals(0, $lesson->exercises->count());
        $this->assertEquals(0, $lesson->exercises_count);

        $this->createExercise(['lesson_id' => $lesson->id]);
        event(new LessonAggregatesUpdated($lesson, $user));

        $this->assertEquals(1, $lesson->exercises()->count());
        $this->assertEquals(1, $lesson->fresh()->exercises_count);
    }

    /** @test */
    public function itShould_handleLessonAggregatesUpdatedEvent_updateChildLessonsCountOfLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();

        $this->assertEquals(0, $lesson->child_lessons_count);
        $lesson->childLessons()->save($this->createLesson());

        event(new LessonAggregatesUpdated($lesson, $user));

        $this->assertEquals(1, $lesson->fresh()->child_lessons_count);
    }
}
