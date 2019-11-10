<?php

namespace Tests\Listeners;

use App\Events\ExerciseCreated;
use App\Listeners\UpdateExercisesCountOfLesson;

class UpdateExercisesCountOfLessonTest extends \TestCase
{
    /** @test */
    public function itShould_updateExercisesCountOfLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();
        $listener = new UpdateExercisesCountOfLesson();
        $event = new ExerciseCreated($lesson, $user);

        $this->assertSame('0', $lesson->fresh()->exercises_count);

        $this->createExercise(['lesson_id' => $lesson->id]);
        $listener->handle($event);
        $this->assertSame('1', $lesson->fresh()->exercises_count);

        $this->createExercise(['lesson_id' => $lesson->id]);
        $listener->handle($event);
        $this->assertSame('2', $lesson->fresh()->exercises_count);
    }

    /** @test */
    public function itShould_updateExercisesCountOfLesson_alsoUpdateParents()
    {
        $user = $this->createUser();

        $grandparentLesson = $this->createLesson();
        $grandparentLesson->subscribe($user);

        $parentLesson = $this->createLesson();
        $parentLesson->subscribe($user);
        $grandparentLesson->childLessons()->attach($parentLesson);

        $childLesson = $this->createLesson();
        $childLesson->subscribe($user);
        $parentLesson->childLessons()->attach($childLesson);

        $listener = new UpdateExercisesCountOfLesson();
        $event = new ExerciseCreated($childLesson, $user);

        $this->assertSame('0', $childLesson->fresh()->exercises_count);
        $this->assertSame('0', $parentLesson->fresh()->exercises_count);
        $this->assertSame('0', $grandparentLesson->fresh()->exercises_count);

        $this->createExercise(['lesson_id' => $childLesson->id]);
        $listener->handle($event);

        $this->assertSame('1', $childLesson->fresh()->exercises_count);
        $this->assertSame('1', $parentLesson->fresh()->exercises_count);
        $this->assertSame('0', $grandparentLesson->fresh()->exercises_count);

        $this->createExercise(['lesson_id' => $childLesson->id]);
        $listener->handle($event);

        $this->assertSame('2', $childLesson->fresh()->exercises_count);
        $this->assertSame('2', $parentLesson->fresh()->exercises_count);
        $this->assertSame('0', $grandparentLesson->fresh()->exercises_count);
    }
}
