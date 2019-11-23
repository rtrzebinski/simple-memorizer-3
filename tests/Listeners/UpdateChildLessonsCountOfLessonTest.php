<?php

namespace Tests\Listeners;

use App\Events\LessonAggregatesUpdated;
use App\Listeners\UpdateChildLessonsCountOfLesson;

class UpdateChildLessonsCountOfLessonTest extends \TestCase
{
    /** @test */
    public function itShould_updateChildLessonsCountOfLesson()
    {
        $listener = new UpdateChildLessonsCountOfLesson();
        $lesson = $this->createLesson();
        $user = $this->createUser();

        $this->assertEquals(0, $lesson->child_lessons_count);

        $lesson->childLessons()->save($this->createLesson());
        $event = new LessonAggregatesUpdated($lesson, $user);
        $listener->handle($event);
        $this->assertEquals(1, $lesson->child_lessons_count);

        $lesson->childLessons()->save($this->createLesson());
        $event = new LessonAggregatesUpdated($lesson, $user);
        $listener->handle($event);
        $this->assertEquals(2, $lesson->child_lessons_count);
    }
}
