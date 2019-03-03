<?php

namespace Tests\Http\Controllers\Api;

use App\Models\Exercise;

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

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises', $input, $user);

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

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises');

        $this->assertUnauthorised();
    }

    public function testItShould_notStoreExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises', $input = [], $user);

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

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises', $input, $user);

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

        $this->callApi('GET', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertResponseOk();
        $this->seeJson($exercise->toArray());
    }

    public function testItShould_notFetchExercise_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('GET', '/exercises/'.$exercise->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notFetchExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('GET', '/exercises/'.$exercise->id, $input = [], $user);

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

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises', $input = [], $user);

        $this->assertResponseOk();
        $this->seeJson([$exercise->toArray()]);
    }

    public function testItShould_notFetchExercisesOfLesson_unauthorised()
    {
        $lesson = $this->createLesson();

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertUnauthorised();
    }

    public function testItShould_notFetchExercisesOfLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises', $input = [], $user);

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

        $this->callApi('PATCH', '/exercises/'.$exercise->id, $input, $user);

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

        $this->callApi('PATCH', '/exercises/'.$exercise->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('PATCH', '/exercises/'.$exercise->id, $input = [], $user);

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

        $this->callApi('PATCH', '/exercises/'.$exercise->id, $input, $user);

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

        $this->callApi('DELETE', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertResponseOk();
        $this->assertNull($exercise->fresh());
    }

    public function testItShould_notDeleteExercise_unauthorised()
    {
        $exercise = $this->createExercise();

        $this->callApi('DELETE', '/exercises/'.$exercise->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notDeleteExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('DELETE', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notDeleteExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', '/exercises/-1', $input = [], $user);

        $this->assertNotFound();
    }
}
