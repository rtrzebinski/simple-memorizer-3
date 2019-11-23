<?php

namespace Tests\Policies;

use App\Policies\UserLessonPolicy;
use App\Structures\UserLesson;

class UserLessonPolicyTest extends \TestCase
{
    // access

    /** @test */
    public function itShould_authorizeUserLessonAccess_publicLesson()
    {
        $user = $this->createUser();
        $userLesson = new UserLesson();
        $userLesson->visibility = 'public';

        $policy = new UserLessonPolicy();

        $result = $policy->access($user, $userLesson);

        $this->assertTrue($result);
    }

    /** @test */
    public function itShould_authorizeUserLessonAccess_ownedLesson()
    {
        $user = $this->createUser();
        $userLesson = new UserLesson();
        $userLesson->visibility = 'private';
        $userLesson->owner_id = $user->id;

        $policy = new UserLessonPolicy();

        $result = $policy->access($user, $userLesson);

        $this->assertTrue($result);
    }

    /** @test */
    public function itShould_authorizeUserLessonAccess_denied()
    {
        $user = $this->createUser();
        $userLesson = new UserLesson();

        $policy = new UserLessonPolicy();

        $result = $policy->access($user, $userLesson);

        $this->assertFalse($result);
    }

    // learn

    /** @test */
    public function itShould_authorizeUserLessonLearn()
    {
        $user = $this->createUser();
        $userLesson = new UserLesson();
        $userLesson->exercises_count = config('app.min_exercises_to_learn_lesson');
        $userLesson->visibility = 'public';

        $policy = new UserLessonPolicy();

        $result = $policy->learn($user, $userLesson);

        $this->assertTrue($result);
    }

    /** @test */
    public function itShould_authorizeUserLessonLearn_noAccess()
    {
        $user = $this->createUser();
        $userLesson = new UserLesson();
        $userLesson->exercises_count = config('app.min_exercises_to_learn_lesson');
        $userLesson->visibility = 'private';

        $policy = new UserLessonPolicy();

        $result = $policy->learn($user, $userLesson);

        $this->assertFalse($result);
    }

    /** @test */
    public function itShould_authorizeUserLessonLearn_notEnoughExercises()
    {
        $user = $this->createUser();
        $userLesson = new UserLesson();
        $userLesson->exercises_count = config('app.min_exercises_to_learn_lesson') - 1;
        $userLesson->visibility = 'public';

        $policy = new UserLessonPolicy();

        $result = $policy->learn($user, $userLesson);

        $this->assertFalse($result);
    }

    // modify

    /** @test */
    public function itShould_authorizeUserLessonModify_userIsOwner()
    {
        $user = $this->createUser();
        $userLesson = new UserLesson();
        $userLesson->owner_id = $user->id;

        $policy = new UserLessonPolicy();

        $result = $policy->modify($user, $userLesson);

        $this->assertTrue($result);
    }

    /** @test */
    public function itShould_authorizeUserLessonModify_userIsNotOwner()
    {
        $user = $this->createUser();
        $userLesson = new UserLesson();

        $policy = new UserLessonPolicy();

        $result = $policy->modify($user, $userLesson);

        $this->assertFalse($result);
    }
}
