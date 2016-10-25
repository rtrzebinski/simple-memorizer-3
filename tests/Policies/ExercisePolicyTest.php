<?php

namespace Tests\Policies;

use App\Policies\ExercisePolicy;
use TestCase;

class ExercisePolicyTest extends TestCase
{
    /**
     * @var ExercisePolicy
     */
    private $policy;

    public function setUp()
    {
        parent::setUp();
        $this->policy = new ExercisePolicy();
    }

    public function testItShould_authorizeExerciseAccess_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->policy->access($user, $exercise));
    }

    public function testItShould_authorizeExerciseAccess_userSubscribesLesson()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();
        $user->subscribedLessons()->attach($exercise->lesson);

        $this->assertTrue($this->policy->access($user, $exercise));
    }

    public function testItShould_notAuthorizeExerciseAccess_userIsNotLessonOwnerAndDoesNotSubscribeIt()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->policy->access($user, $exercise));
    }

    public function testItShould_authorizeExerciseModify_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->policy->modify($user, $exercise));
    }

    public function testItShould_notAuthorizeExerciseModify_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->policy->modify($user, $exercise));
    }
}
