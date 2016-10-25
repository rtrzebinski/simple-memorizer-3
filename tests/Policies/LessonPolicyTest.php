<?php

namespace Tests\Policies;

use App\Models\Lesson\Lesson;
use App\Models\User\User;
use App\Policies\LessonPolicy;
use TestCase;

class LessonPolicyTest extends TestCase
{
    /**
     * @var LessonPolicy
     */
    private $policy;

    public function setUp()
    {
        parent::setUp();
        $this->policy = new LessonPolicy;
    }

    public function testItShould_authorizeLessonAccess_userIsLessonOwner()
    {
        $lesson = $this->createLesson();
        $user = $lesson->owner;

        $this->assertTrue($this->policy->access($user, $lesson));
    }

    public function testItShould_authorizeLessonAccess_userSubscribesLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();
        $user->subscribedLessons()->attach($lesson);

        $this->assertTrue($this->policy->access($user, $lesson));
    }

    public function testItShould_notAuthorizeLessonAccess_userIsNotLessonOwnerAndDoesNotSubscribeIt()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();

        $this->assertFalse($this->policy->access($user, $lesson));
    }

    public function testItShould_authorizeLessonSubscribe_publicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertTrue($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_authorizeLessonSubscribe_publicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertTrue($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_authorizeLessonSubscribe_privateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeLessonSubscribe_privateAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeLessonSubscribe_userAlreadySubscribedPublicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeLessonSubscribe_userAlreadySubscribedPublicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeLessonSubscribe_userAlreadySubscribedPrivateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_authorizeLessonUnsubscribe_userSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->assertTrue($this->policy->unsubscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeLessonUnsubscribe_userDoesNotSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertFalse($this->policy->unsubscribe($user, $lesson));
    }

    public function testItShould_authorizeLessonModify_userIsLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->policy->modify($user, $lesson));
    }

    public function testItShould_notAuthorizeLessonModify_userIsNotLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->modify($user, $lesson));
    }
}
