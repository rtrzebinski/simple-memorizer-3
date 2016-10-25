<?php

namespace Tests\Models\Users;

use TestCase;

class InteractsWithLessonsTest extends TestCase
{
    public function testItShould_fetchSubscribedLessons()
    {
        $user = $this->createUser();
        $ownedLesson = $this->createPublicLesson($user);
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribers()->save($user);
        $this->createPublicLesson();
        $this->createPrivateLesson(); // not available

        $this->assertCount(1, $user->ownedLessons);
        $this->assertEquals($ownedLesson->id, $user->ownedLessons[0]->id);
    }

    public function testItShould_fetchOwnedLessons()
    {
        $user = $this->createUser();
        $this->createPublicLesson($user);
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribers()->save($user);
        $this->createPublicLesson();
        $this->createPrivateLesson(); // not available

        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($subscribedLesson->id, $user->subscribedLessons[0]->id);
    }

    public function testItShould_fetchAvailableLessons()
    {
        $user = $this->createUser();
        $this->createPublicLesson($user);
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribers()->save($user);
        $availableLesson = $this->createPublicLesson();
        $this->createPrivateLesson(); // not available

        $this->assertCount(1, $user->availableLessons());
        $this->assertEquals($availableLesson->id, $user->availableLessons()[0]->id);
    }

    public function testIt_hasOwnedOrSubscribedLessons_ownedLesson()
    {
        $user = $this->createUser();
        $this->createPublicLesson($user);

        $this->assertTrue($user->hasOwnedOrSubscribedLessons());
    }

    public function testIt_hasOwnedOrSubscribedLessons_subscribedLesson()
    {
        $user = $this->createUser();
        $this->createPublicLesson()->subscribers()->save($user);

        $this->assertTrue($user->hasOwnedOrSubscribedLessons());
    }

    public function testIt_hasNoOwnedOrSubscribedLessons()
    {
        $user = $this->createUser();

        $this->assertFalse($user->hasOwnedOrSubscribedLessons());
    }

}
