<?php

namespace Tests\Http\Controllers\Api;

use App\Models\User;
use App\Services\LearningService;
use App\Structures\UserLesson;
use PHPUnit\Framework\MockObject\MockObject;

class LearningControllerTest extends BaseTestCase
{

    // fetchRandomExerciseOfLesson

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise = $this->createExercise();

        $userExercise = $this->createUserExercise($user, $exercise);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('fetchRandomExerciseOfLesson')
            ->with($this->isInstanceOf(UserLesson::class), $this->isInstanceOf(User::class), $previous->id)
            ->willReturn($userExercise);

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random',
            ['previous_exercise_id' => $previous->id], $user);

        $this->assertResponseOk();
        $this->seeJsonFragment((array)$userExercise);
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_unauthorized()
    {
        $this->callApi('GET', '/lessons/'.$this->createLesson()->id.'/exercises/random', $data = []);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_forbidden()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/'.$this->createPrivateLesson()->id.'/exercises/random', $data = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises/random', $data = [], $user);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_invalidPreviousExerciseId()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random',
            ['previous_exercise_id' => -1], $user);

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_noExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('fetchRandomExerciseOfLesson')
            ->with($this->isInstanceOf(UserLesson::class), $this->isInstanceOf(User::class))
            ->willReturn(null);

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random', [], $user);

        $this->assertResponseOk();
        $this->seeJson([]);
    }

    // handleGoodAnswer

    /** @test */
    public function itShould_handleGoodAnswer()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-good-answer', $data = [], $user);

        $this->assertResponseOk();

        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
        // just one good and no bad answers - predefined 20%
        $this->assertEquals(20, $exercise->percentOfGoodAnswers($user->id));
        // 10 because 2 exercises are required to learn a lesson,
        // so one will be 0%, another will be 20%
        // (0 + 20) / 2 = 10
        $this->assertEquals(10, $lesson->percentOfGoodAnswers($user->id));
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-good-answer');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/handle-good-answer', $data =
            [], $user);

        $this->assertResponseNotFound();
    }

    // handleBadAnswer

    /** @test */
    public function itShould_handleBadAnswer()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-bad-answer', $data = [], $user);

        $this->assertResponseOk();

        $this->assertEquals(1, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswers($user->id));
        $this->assertEquals(0, $lesson->percentOfGoodAnswers($user->id));
    }

    /** @test */
    public function itShould_notHandleBadAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-bad-answer');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notHandleBadAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/handle-bad-answer', $data =
            [], $user);

        $this->assertResponseNotFound();
    }
}
