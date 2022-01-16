<?php

namespace App\Listeners;

use App\Events\LessonEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateSubscribersCountOfLesson
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
     * @param LessonEvent $event
     * @return void
     */
    public function handle(LessonEvent $event): void
    {
        $lesson = $event->lesson();

        $lesson->subscribers_count = $lesson->subscribedUsers()->count();
        $lesson->save();
    }
}
