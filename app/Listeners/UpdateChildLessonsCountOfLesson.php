<?php

namespace App\Listeners;

use App\Events\LessonEventInterface;
use App\Models\Lesson;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateChildLessonsCountOfLesson
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param LessonEventInterface $event
     * @return void
     */
    public function handle(LessonEventInterface $event): void
    {
        $lesson = $event->lesson();

        $this->updateExercisesCountOfLesson($lesson);
    }

    /**
     * @param Lesson $lesson
     */
    private function updateExercisesCountOfLesson(Lesson $lesson): void
    {
        $lesson->child_lessons_count = $lesson->childLessons()->count();
        $lesson->save();
    }
}
