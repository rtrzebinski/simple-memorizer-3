<?php

namespace Tests\Models\Users;

use TestCase;

/**
 * @property \App\Models\User\User user
 * @property \App\Models\Lesson\Lesson ownedLesson
 * @property \App\Models\Lesson\Lesson subscribedLesson
 * @property \App\Models\Lesson\Lesson availableLesson
 */
class InteractsWithLessonsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->ownedLesson = $this->createPublicLesson($this->user);
        $this->subscribedLesson = $this->createPublicLesson();
        $this->subscribedLesson->subscribers()->save($this->user);
        $this->availableLesson = $this->createPublicLesson();
        $this->createPrivateLesson(); // not available
    }

    public function testItShould_fetchSubscribedLessons()
    {
        $this->assertCount(1, $this->user->ownedLessons);
        $this->assertEquals($this->ownedLesson->id, $this->user->ownedLessons[0]->id);
    }

    public function testItShould_fetchOwnedLessons()
    {
        $this->assertCount(1, $this->user->subscribedLessons);
        $this->assertEquals($this->subscribedLesson->id, $this->user->subscribedLessons[0]->id);
    }

    public function testItShould_fetchAvailableLessons()
    {
        $this->assertCount(1, $this->user->availableLessons());
        $this->assertEquals($this->availableLesson->id, $this->user->availableLessons()[0]->id);
    }
}
