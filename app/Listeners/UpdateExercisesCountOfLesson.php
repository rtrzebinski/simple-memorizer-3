<?php

namespace App\Listeners;

use App\Events\LessonEventInterface;
use App\Models\Lesson;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateExercisesCountOfLesson
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
    public function handle(LessonEventInterface $event)
    {
        $lesson = $event->lesson();

        $this->updateExercisesCountOfLesson($lesson);
    }

    private function updateExercisesCountOfLesson(Lesson $lesson)
    {
        $lesson->exercises_count = $lesson->allExercises()->count();
        $lesson->save();

        // recursively run for each parent lesson
        foreach ($lesson->parentLessons as $parentLesson) {
            $this->updateExercisesCountOfLesson($parentLesson);
        }
    }
}
