<?php

namespace Tests\Models;

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

    /** @test */
    public function itShould_fetchAvailableLessons()
    {
        $user = $this->createUser();
        $this->createPublicLesson($user);
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribedUsers()->save($user);
        $availableLesson = $this->createPublicLesson();
        $this->createPrivateLesson(); // not available

        $this->assertCount(1, $user->availableLessons());
        $this->assertEquals($availableLesson->id, $user->availableLessons()[0]->id);
    }

    /** @test */
    public function itShould_notFetchAvailableLessons_lessonSubscribedByMeAndOtherUser()
    {
        $user = $this->createUser();
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribedUsers()->save($user);
        $subscribedLesson->subscribedUsers()->save($this->createUser());

        $this->assertCount(0, $user->availableLessons());
    }

    /** @test */
    public function itShould_fetchAvailableLessons_lessonSubscribedByOtherUserOnly()
    {
        $user = $this->createUser();
        $subscribedLesson = $this->createPublicLesson();
        $subscribedLesson->subscribedUsers()->save($this->createUser());

        $this->assertCount(1, $user->availableLessons());
    }

    /** @test */
    public function it_hasOwnedOrSubscribedLessons_ownedLesson()
    {
        $user = $this->createUser();
        $this->createPublicLesson($user);

        $this->assertTrue($user->hasOwnedOrSubscribedLessons());
    }

    /** @test */
    public function it_hasOwnedOrSubscribedLessons_subscribedLesson()
    {
        $user = $this->createUser();
        $this->createPublicLesson()->subscribedUsers()->save($user);

        $this->assertTrue($user->hasOwnedOrSubscribedLessons());
    }

    /** @test */
    public function it_hasNoOwnedOrSubscribedLessons()
    {
        $user = $this->createUser();

        $this->assertFalse($user->hasOwnedOrSubscribedLessons());
    }
}
