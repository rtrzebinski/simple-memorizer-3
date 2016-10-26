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
        $lesson = $this->createPrivateLesson();

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

    public function testItShould_fetchRandomExerciseOfLesson_withPreviousExerciseId()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random',
            ['previous_exercise_id' => $previous->id], $user);

        $decodedResponse = json_decode($this->response->content());

        $this->assertTrue($exercise1->id == $decodedResponse->id || $exercise2->id == $decodedResponse->id);
    }

    public function testItShould_fetchRandomExerciseOfLesson_withoutPreviousExerciseId()
    {
        $user = $this->createUser()->fresh();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();
        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random', $data = [], $user);

        $decodedResponse = json_decode($this->response->content());

        $this->assertTrue($exercise1->id == $decodedResponse->id || $exercise2->id == $decodedResponse->id);
    }

    public function testItShould_notFetchRandomExerciseOfLesson_unauthorized()
    {
        $this->callApi('GET', '/lessons/' . $this->createLesson()->id . '/exercises/random', $data = []);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notFetchRandomExerciseOfLesson_forbidden()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/' . $this->createPrivateLesson()->id . '/exercises/random', $data = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notFetchRandomExerciseOfLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises/random', $data = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testItShould_notFetchRandomExerciseOfLesson_invalidPreviousExerciseId()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random',
            ['previous_exercise_id' => -1], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_increaseNumberOfGoodAnswersOfUser()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('POST', '/exercises/' . $exercise->id . '/increase-number-of-good-answers-of-user', $data =
            [], $user);

        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_notIncreaseNumberOfGoodAnswersOfUser_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/increase-number-of-good-answers-of-user');

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notIncreaseNumberOfGoodAnswersOfUser_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/increase-number-of-good-answers-of-user', $data =
            [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notIncreaseNumberOfGoodAnswersOfUser_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/increase-number-of-good-answers-of-user', $data =
            [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testItShould_increaseNumberOfBadAnswersOfUser()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('POST', '/exercises/' . $exercise->id . '/increase-number-of-bad-answers-of-user', $data =
            [], $user);

        $this->assertEquals(1, $exercise->numberOfBadAnswersOfUser($user->id));
    }

    public function testItShould_notIncreaseNumberOfBadAnswersOfUser_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/increase-number-of-bad-answers-of-user');

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notIncreaseNumberOfBadAnswersOfUser_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/increase-number-of-bad-answers-of-user', $data =
            [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notIncreaseNumberOfBadAnswersOfUser_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/increase-number-of-bad-answers-of-user', $data =
            [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }
}
