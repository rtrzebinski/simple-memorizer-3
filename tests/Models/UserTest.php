<?php

namespace Tests\Models;

class UserTest extends \TestCase
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

    public function testItShould_notFetchAvailableLessons_lessonSubscribedByMeAndOtherUser()
    {
        $user = $this->createUser();
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribers()->save($user);
        $subscribedLesson->subscribers()->save($this->createUser());

        $this->assertCount(0, $user->availableLessons());
    }

    public function testItShould_fetchAvailableLessons_lessonSubscribedByOtherUserOnly()
    {
        $user = $this->createUser();
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribers()->save($this->createUser());

        $this->assertCount(1, $user->availableLessons());
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
