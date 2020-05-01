<?php

namespace Tests\Unit\Policies;

use App\Policies\UserExercisePolicy;

class UserExercisePolicyTest extends \TestCase
{
    private UserExercisePolicy $policy;

    public function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserExercisePolicy();
    }

    // access

    /** @test */
    public function itShould_accessUserExercise_exerciseBelongsToSubscribedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->policy->access($user, $userExercise);

        $this->assertTrue($result);
    }

    /** @test */
    public function itShould_notAccessUserExercise_exerciseDoesNotBelongToSubscribedLesson()
    {
        $user = $this->createUser();
        $userExercise = $this->createUserExercise($user, $this->createExercise());

        $result = $this->policy->access($user, $userExercise);

        $this->assertFalse($result);
    }
}
