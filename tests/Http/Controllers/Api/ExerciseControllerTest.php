<?php

namespace Tests\Http\Controllers\Api;

use Illuminate\Http\Response;
use TestCase;

class ExerciseControllerTest extends TestCase
{
    public function testItShould_createExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input, $user);

        $this->assertResponseStatus(Response::HTTP_OK);

        $this->seeJson([
            'question' => $input['question'],
            'answer' => $input['answer'],
            'lesson_id' => $lesson->id,
        ]);
    }

    public function testItShould_notCreateExercise_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises');

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notCreateExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notCreateExercise_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input, $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notCreateExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/lessons/-1/exercises', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testItShould_fetchExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->seeJson($exercise->toArray());
    }

    public function testItShould_notFetchExercise_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('GET', '/exercises/' . $exercise->id);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notFetchExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('GET', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notFetchExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/exercises/-1', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testItShould_fetchExercisesOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->seeJson([$exercise->toArray()]);
    }

    public function testItShould_notFetchExercisesOfLesson_unauthorised()
    {
        $lesson = $this->createLesson();

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises');

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notFetchExercisesOfLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notFetchExercisesOfLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

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

        $this->assertResponseStatus(Response::HTTP_OK);

        $this->seeJson([
            'question' => $input['question'],
            'answer' => $input['answer'],
        ]);
    }

    public function testItShould_notUpdateExercise_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('PATCH', '/exercises/' . $exercise->id);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
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

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notUpdateExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('PATCH', '/exercises/-1', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

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

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notDeleteExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('DELETE', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notDeleteExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', '/exercises/-1', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }
}
