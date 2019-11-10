<?php

namespace Tests\Policies;

use App\Policies\LessonPolicy;
use TestCase;

class LessonPolicyTest extends TestCase
{
    /**
     * @var LessonPolicy
     */
    private $policy;

    public function setUp(): void
    {
        parent::setUp();
        $this->policy = new LessonPolicy;
    }

    // access

    /** @test */
    public function itShould_authorizeLessonAccess_userIsLessonOwner()
    {
        $lesson = $this->createLesson();
        $user = $lesson->owner;

        $this->assertTrue($this->policy->access($user, $lesson));
    }

    /** @test */
    public function itShould_authorizeLessonAccess_lessonIsPublic()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertTrue($this->policy->access($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonAccess_lessonIsPrivate()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->access($user, $lesson));
    }

    /** @test */
    public function itShould_authorizeLessonAccess_lessonIsPublic_guestUser()
    {
        $lesson = $this->createPublicLesson();

        $this->assertTrue($this->policy->access($user = null, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonAccess_lessonIsPrivate_guestUser()
    {
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->access($user = null, $lesson));
    }

    // subscribe

    /** @test */
    public function itShould_authorizeLessonSubscribe_publicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertTrue($this->policy->subscribe($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonSubscribe_publicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonSubscribe_privateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    /** @test */
    public function itShould_authorizeLessonSubscribe_lessonWasSubscribedByAnotherUser()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->createUser()->subscribedLessons()->save($lesson);

        $this->assertTrue($this->policy->subscribe($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonSubscribe_privateAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonSubscribe_userAlreadySubscribedPublicAndOwnedLesson()
    {
        $user = $this->createUser();
        // will also subscribe owner to the lesson
        $lesson = $this->createPublicLesson($user);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonSubscribe_userAlreadySubscribedPublicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonSubscribe_userAlreadySubscribedPrivateAndOwnedLesson()
    {
        $user = $this->createUser();
        // will also subscribe owner to the lesson
        $lesson = $this->createPrivateLesson($user);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    // unsubscribe

    /** @test */
    public function itShould_authorizeLessonUnsubscribe_userSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->assertTrue($this->policy->unsubscribe($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonUnsubscribe_userDoesNotSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertFalse($this->policy->unsubscribe($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonUnsubscribe_userOwnsLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertFalse($this->policy->unsubscribe($user, $lesson));
    }

    // modify

    /** @test */
    public function itShould_authorizeLessonModify_userIsLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->policy->modify($user, $lesson));
    }

    /** @test */
    public function itShould_notAuthorizeLessonModify_userIsNotLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->modify($user, $lesson));
    }

    // learn

    /** @test */
    public function itShould_authorizeLessonLearn()
    {
        $lesson = $this->createLesson();
        $user = $lesson->owner;
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->assertTrue($this->policy->learn($user, $lesson->fresh()));
    }

    /** @test */
    public function itShould_notAuthorizeLessonLearn_noAccess()
    {
        $lesson = $this->createPrivateLesson();
        $user = $this->createUser();
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->assertFalse($this->policy->learn($user, $lesson->fresh()));
    }

    /** @test */
    public function itShould_notAuthorizeLessonLearn_notEnoughExercises()
    {
        $lesson = $this->createLesson();
        $user = $lesson->owner;

        $this->assertFalse($this->policy->learn($user, $lesson->fresh()));
    }
}
