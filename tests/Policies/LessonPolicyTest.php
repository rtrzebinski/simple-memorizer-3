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

    public function testItShould_authorizeSubscribeLesson_publicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertTrue($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_authorizeSubscribeLesson_publicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertTrue($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_authorizeSubscribeLesson_privateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeSubscribeLesson_privateAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeSubscribeLesson_userAlreadySubscribedPublicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeSubscribeLesson_userAlreadySubscribedPublicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeSubscribeLesson_userAlreadySubscribedPrivateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->policy->subscribe($user, $lesson));
    }

    public function testItShould_authorizeUnsubscribeLesson_userSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->assertTrue($this->policy->unsubscribe($user, $lesson));
    }

    public function testItShould_notAuthorizeUnsubscribeLesson_userDoesNotSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertFalse($this->policy->unsubscribe($user, $lesson));
    }

    public function testItShould_authorizeUpdateLesson_userIsLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->policy->update($user, $lesson));
    }

    public function testItShould_notAuthorizeUpdateLesson_userIsNotLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->update($user, $lesson));
    }

    public function testItShould_authorizeDeleteLesson_userIsLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->policy->delete($user, $lesson));
    }

    public function testItShould_notAuthorizeDeleteLesson_userIsNotLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->policy->delete($user, $lesson));
    }

    public function testItShould_authorizeCreateExercise_userIsLessonOwner()
    {
        $lesson = $this->createLesson();
        $user = $lesson->owner;

        $this->assertTrue($this->policy->createExercise($user, $lesson));
    }

    public function testItShould_notAuthorizeCreateExercise_userIsNotLessonOwner()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();

        $this->assertFalse($this->policy->createExercise($user, $lesson));
    }

    public function testItShould_authorizeFetchExercisesOfLesson_userIsLessonOwner()
    {
        $lesson = $this->createLesson();
        $user = $lesson->owner;

        $this->assertTrue($this->policy->fetchExercisesOfLesson($user, $lesson));
    }

    public function testItShould_authorizeFetchExercisesOfLesson_userSubscribesLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();
        $user->subscribedLessons()->attach($lesson);

        $this->assertTrue($this->policy->fetchExercisesOfLesson($user, $lesson));
    }

    public function testItShould_notAuthorizeFetchExercisesOfLesson_userIsNotLessonOwnerAndDoesNotSubscribeIt()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();

        $this->assertFalse($this->policy->fetchExercisesOfLesson($user, $lesson));
    }

    protected function createPublicLesson(User $user = null) : Lesson
    {
        $attributes = ['visibility' => 'public'];

        if ($user) {
            $attributes['owner_id'] = $user->id;
        }

        return $this->createLesson($attributes);
    }

    protected function createPrivateLesson(User $user = null) : Lesson
    {
        $attributes = ['visibility' => 'private'];

        if ($user) {
            $attributes['owner_id'] = $user->id;
        }

        return $this->createLesson($attributes);
    }
}
