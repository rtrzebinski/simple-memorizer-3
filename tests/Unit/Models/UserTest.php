<?php

namespace Tests\Unit\Models;

class UserTest extends \TestCase
{
    /** @test */
    public function itShould_fetchSubscribedLessons()
    {
        $user = $this->createUser();
        $ownedLesson = $this->createPublicLesson($user);
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribedUsers()->save($user);
        $this->createPublicLesson();
        $this->createPrivateLesson(); // not available

        $this->assertCount(1, $user->ownedLessons);
        $this->assertEquals($ownedLesson->id, $user->ownedLessons[0]->id);
    }

    /** @test */
    public function itShould_fetchOwnedLessons()
    {
        $user = $this->createUser();
        $this->createPublicLesson($user);
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribedUsers()->save($user);
        $this->createPublicLesson();
        $this->createPrivateLesson(); // not available

        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($subscribedLesson->id, $user->subscribedLessons[0]->id);
    }
}
