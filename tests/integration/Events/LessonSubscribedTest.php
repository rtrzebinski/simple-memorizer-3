<?php

namespace Tests\Integration\Events;

use App\Events\LessonSubscribed;

class LessonSubscribedTest extends \TestCase
{
    /** @test */
    public function itShould_handleLessonSubscribedEvent_updateSubscribersCountOfLesson()
    {
        $lesson = $this->createLesson();

        $this->assertEquals(0, $lesson->fresh()->subscribers_count);

        $lesson->subscribedUsers()->save($user = $this->createUser());
        event(new LessonSubscribed($lesson, $user));

        $this->assertEquals(1, $lesson->fresh()->subscribers_count);
    }
}
