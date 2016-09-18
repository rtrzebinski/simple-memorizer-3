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

    public function testItShould_authorizeFetchExerciseById_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->policy->fetch($user, $exercise));
    }

    public function testItShould_authorizeFetchExerciseById_userSubscribesLesson()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();
        $user->subscribedLessons()->attach($exercise->lesson);

        $this->assertTrue($this->policy->fetch($user, $exercise));
    }

    public function testItShould_notAuthorizeFetchExerciseById_userIsNotLessonOwnerAndDoesNotSubscribeIt()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->policy->fetch($user, $exercise));
    }

    public function testItShould_authorizeUpdateExercise_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->policy->update($user, $exercise));
    }

    public function testItShould_notAuthorizeUpdateExercise_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->policy->update($user, $exercise));
    }

    public function testItShould_authorizeDeleteExercise_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->policy->delete($user, $exercise));
    }

    public function testItShould_notAuthorizeDeleteExercise_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->policy->delete($user, $exercise));
    }

    public function testItShould_authorizeAnswerQuestion_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->policy->answerQuestion($user, $exercise));
    }

    public function testItShould_authorizeAnswerQuestion_userSubscribesLesson()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();
        $user->subscribedLessons()->attach($exercise->lesson);

        $this->assertTrue($this->policy->answerQuestion($user, $exercise));
    }

    public function testItShould_notAuthorizeAnswerQuestion_userIsNotLessonOwnerAndDoesNotSubscribeIt()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->policy->answerQuestion($user, $exercise));
    }
}
