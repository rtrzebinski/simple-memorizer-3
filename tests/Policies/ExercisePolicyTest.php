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

    public function setUp(): void
    {
        parent::setUp();
        $this->policy = new ExercisePolicy();
    }

    /** @test */
    public function itShould_authorizeExerciseAccess_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->policy->access($user, $exercise));
    }

    /** @test */
    public function itShould_authorizeExerciseAccess_userSubscribesLesson()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();
        $user->subscribedLessons()->attach($exercise->lesson);

        $this->assertTrue($this->policy->access($user, $exercise));
    }

    /** @test */
    public function itShould_notAuthorizeExerciseAccess_userIsNotLessonOwnerAndDoesNotSubscribeIt()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->policy->access($user, $exercise));
    }

    /** @test */
    public function itShould_authorizeExerciseModify_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->policy->modify($user, $exercise));
    }

    /** @test */
    public function itShould_notAuthorizeExerciseModify_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->policy->modify($user, $exercise));
    }
}
