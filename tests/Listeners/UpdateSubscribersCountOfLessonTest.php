<?php

namespace Tests\Listeners;

use App\Events\LessonSubscribed;
use App\Events\LessonUnsubscribed;
use App\Listeners\UpdateSubscribersCountOfLesson;

class UpdateSubscribersCountOfLessonTest extends \TestCase
{
    /** @test */
    public function itShould_updateSubscribersCountOfLesson()
    {
        $lesson = $this->createLesson();
        $listener = new UpdateSubscribersCountOfLesson();

        $this->assertEquals(0, $lesson->fresh()->subscribers_count);

        $lesson->subscribedUsers()->save($user = $this->createUser());
        $event = new LessonSubscribed($lesson, $user);
        $listener->handle($event);

        $this->assertEquals(1, $lesson->fresh()->subscribers_count);

        $lesson->subscribedUsers()->save($user = $this->createUser());

        $event = new LessonSubscribed($lesson, $user);
        $listener->handle($event);

        $this->assertEquals(2, $lesson->fresh()->subscribers_count);

        $lesson->subscribedUsers()->detach($user);
        $event = new LessonUnsubscribed($lesson, $user);
        $listener->handle($event);

        $this->assertEquals(1, $lesson->fresh()->subscribers_count);
    }
}
