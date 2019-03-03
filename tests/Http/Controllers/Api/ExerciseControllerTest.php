<?php

namespace Tests\Http\Controllers\Api;

use App\Exceptions\NotEnoughExercisesException;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\LearningService;

class ExerciseControllerTest extends BaseTestCase
{
    // storeExercise

    public function testItShould_storeExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input, $user);

        $this->assertResponseOk();

        $this->seeJson([
            'question' => $input['question'],
            'answer' => $input['answer'],
            'lesson_id' => $lesson->id,
        ]);

        /** @var Exercise $exercise */
        $exercise = $this->last(Exercise::class);
        $this->assertEquals($input['question'], $exercise->question);
        $this->assertEquals($input['answer'], $exercise->answer);
        $this->assertEquals($lesson->id, $exercise->lesson_id);
    }

    public function testItShould_notStoreExercise_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises');

        $this->assertUnauthorised();
    }

    public function testItShould_notStoreExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input = [], $user);

        $this->assertInvalidInput();
    }

    public function testItShould_notStoreExercise_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input, $user);

        $this->assertForbidden();
    }

    public function testItShould_notStoreExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/lessons/-1/exercises', $input = [], $user);

        $this->assertNotFound();
    }

    // fetchExercise

    public function testItShould_fetchExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseOk();
        $this->seeJson($exercise->toArray());
    }

    public function testItShould_notFetchExercise_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('GET', '/exercises/' . $exercise->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notFetchExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('GET', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notFetchExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/exercises/-1', $input = [], $user);

        $this->assertNotFound();
    }

    // fetchExercisesOfLesson

    public function testItShould_fetchExercisesOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises', $input = [], $user);

        $this->assertResponseOk();
        $this->seeJson([$exercise->toArray()]);
    }

    public function testItShould_notFetchExercisesOfLesson_unauthorised()
    {
        $lesson = $this->createLesson();

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises');

        $this->assertUnauthorised();
    }

    public function testItShould_notFetchExercisesOfLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises', $input = [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notFetchExercisesOfLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises', $input = [], $user);

        $this->assertNotFound();
    }

    // updateExercise

    public function testItShould_updateExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input, $user);

        $this->assertResponseOk();

        $this->seeJson([
            'question' => $input['question'],
            'answer' => $input['answer'],
        ]);

        /** @var Exercise $exercise */
        $exercise = $exercise->fresh();
        $this->assertEquals($input['question'], $exercise->question);
        $this->assertEquals($input['answer'], $exercise->answer);
    }

    public function testItShould_notUpdateExercise_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('PATCH', '/exercises/' . $exercise->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertInvalidInput();
    }

    public function testItShould_notUpdateExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input, $user);

        $this->assertForbidden();
    }

    public function testItShould_notUpdateExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('PATCH', '/exercises/-1', $input = [], $user);

        $this->assertNotFound();
    }

    // deleteExercise

    public function testItShould_deleteExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('DELETE', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseOk();
        $this->assertNull($exercise->fresh());
    }

    public function testItShould_notDeleteExercise_unauthorised()
    {
        $exercise = $this->createExercise();

        $this->callApi('DELETE', '/exercises/' . $exercise->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notDeleteExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('DELETE', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notDeleteExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', '/exercises/-1', $input = [], $user);

        $this->assertNotFound();
    }

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

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random',
            ['previous_exercise_id' => $previous->id], $user);

        $this->assertResponseOk();
        $this->seeJson($exercise->toArray());
    }

    public function testItShould_notFetchRandomExerciseOfLesson_unauthorized()
    {
        $this->callApi('GET', '/lessons/' . $this->createLesson()->id . '/exercises/random', $data = []);

        $this->assertUnauthorised();
    }

    public function testItShould_notFetchRandomExerciseOfLesson_forbidden()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/' . $this->createPrivateLesson()->id . '/exercises/random', $data = [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notFetchRandomExerciseOfLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises/random', $data = [], $user);

        $this->assertNotFound();
    }

    public function testItShould_notFetchRandomExerciseOfLesson_invalidPreviousExerciseId()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random',
            ['previous_exercise_id' => -1], $user);

        $this->assertInvalidInput();
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

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random', [], $user);

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

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-good-answer', $data =
            [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notHandleGoodAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-good-answer');

        $this->assertUnauthorised();
    }

    public function testItShould_notHandleGoodAnswer_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-good-answer', $data =
            [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notHandleGoodAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/handle-good-answer', $data =
            [], $user);

        $this->assertNotFound();
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

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-bad-answer', $data =
            [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notHandleBadAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-bad-answer');

        $this->assertUnauthorised();
    }

    public function testItShould_notHandleBadAnswer_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-bad-answer', $data =
            [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notHandleBadAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/handle-bad-answer', $data =
            [], $user);

        $this->assertNotFound();
    }
}
