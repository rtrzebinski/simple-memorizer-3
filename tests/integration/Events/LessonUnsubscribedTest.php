<?php

namespace Tests\Integration\Events;

use App\Events\LessonUnsubscribed;

class LessonUnsubscribedTest extends \TestCase
{
    /** @test */
    public function itShould_handleLessonUnsubscribedEvent_updateSubscribersCountOfLesson()
    {
        $lesson = $this->createLesson();

        $this->assertEquals(0, $lesson->fresh()->subscribers_count);

        $lesson->subscribedUsers()->save($user = $this->createUser());
        event(new LessonUnsubscribed($lesson, $user));

        $this->assertEquals(1, $lesson->fresh()->subscribers_count);
    }
}
