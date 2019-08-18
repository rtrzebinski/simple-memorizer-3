<?php

namespace Tests\Http\Controllers\Api;

use App\Exceptions\NotEnoughExercisesException;
use App\Models\Lesson;
use App\Services\LearningService;

class LearningControllerTest extends BaseTestCase
{

    // fetchRandomExerciseOfLesson

    public function testItShould_fetchRandomExerciseOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise = $this->createExercise();

        /** @var LearningService|\PHPUnit_Framework_MockObject_MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('fetchRandomExerciseOfLesson')
            ->with($this->isInstanceOf(Lesson::class), $user->id, $previous->id)
            ->willReturn($exercise);

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random',
            ['previous_exercise_id' => $previous->id], $user);

        $this->assertResponseOk();
        $this->seeJsonFragment($exercise->toArray());
    }

    public function testItShould_notFetchRandomExerciseOfLesson_unauthorized()
    {
        $this->callApi('GET', '/lessons/'.$this->createLesson()->id.'/exercises/random', $data = []);

        $this->assertResponseUnauthorised();
    }

    public function testItShould_notFetchRandomExerciseOfLesson_forbidden()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/'.$this->createPrivateLesson()->id.'/exercises/random', $data = [], $user);

        $this->assertResponseForbidden();
    }

    public function testItShould_notFetchRandomExerciseOfLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises/random', $data = [], $user);

        $this->assertResponseNotFound();
    }

    public function testItShould_notFetchRandomExerciseOfLesson_invalidPreviousExerciseId()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random',
            ['previous_exercise_id' => -1], $user);

        $this->assertResponseInvalidInput();
    }

    public function testItShould_notFetchRandomExerciseOfLesson_notEnoughExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        /** @var LearningService|\PHPUnit_Framework_MockObject_MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('fetchRandomExerciseOfLesson')
            ->with($this->isInstanceOf(Lesson::class), $user->id)
            ->willThrowException(new NotEnoughExercisesException());

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random', [], $user);

        $this->assertResponseStatus(NotEnoughExercisesException::HTTP_RESPONSE_CODE);
    }

    // handleGoodAnswer

    public function testItShould_handleGoodAnswer()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        /** @var LearningService|\PHPUnit_Framework_MockObject_MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->expects($this->once())->method('handleGoodAnswer')
            ->with($exercise->id, $user->id);

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-good-answer', $data =
            [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notHandleGoodAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-good-answer');

        $this->assertResponseUnauthorised();
    }

    public function testItShould_notHandleGoodAnswer_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-good-answer', $data =
            [], $user);

        $this->assertResponseForbidden();
    }

    public function testItShould_notHandleGoodAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/handle-good-answer', $data =
            [], $user);

        $this->assertResponseNotFound();
    }

    // handleBadAnswer

    public function testItShould_handleBadAnswer()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        /** @var LearningService|\PHPUnit_Framework_MockObject_MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->expects($this->once())->method('handleBadAnswer')
            ->with($exercise->id, $user->id);

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-bad-answer', $data =
            [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notHandleBadAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-bad-answer');

        $this->assertResponseUnauthorised();
    }

    public function testItShould_notHandleBadAnswer_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-bad-answer', $data =
            [], $user);

        $this->assertResponseForbidden();
    }

    public function testItShould_notHandleBadAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/handle-bad-answer', $data =
            [], $user);

        $this->assertResponseNotFound();
    }
}
