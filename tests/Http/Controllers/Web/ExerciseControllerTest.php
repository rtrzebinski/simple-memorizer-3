<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Exercise\Exercise;

class ExerciseControllerTest extends BaseTestCase
{
    // create

    public function testItShould_showExerciseCreatePage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
    }

    public function testItShould_notShowExerciseCreatePage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create');

        $this->assertUnauthorized();
    }

    public function testItShould_notShowExerciseCreatePage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create');

        $this->assertForbidden();
    }

    public function testItShould_notShowExerciseCreatePage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/exercises/create');

        $this->assertNotFound();
    }

    // store

    public function testItShould_storeExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises', $parameters);

        /** @var Exercise $exercise */
        $exercise = $this->last(Exercise::class);
        $this->assertEquals($parameters['question'], $exercise->question);
        $this->assertEquals($parameters['answer'], $exercise->answer);
        $this->assertRedirectedTo('/lessons/' . $lesson->id);
    }

    public function testItShould_notStoreExercise_unauthorized()
    {
        $lesson = $this->createLesson();

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises', $parameters);

        $this->assertUnauthorized();
    }

    public function testItShould_notStoreExercise_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises', $parameters);

        $this->assertForbidden();
    }

    public function testItShould_notStoreExercise_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('POST', '/lessons/-1/exercises', $parameters);

        $this->assertNotFound();
    }

    public function testItShould_notStoreExercise_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises');

        $this->assertInvalidInput();
    }

    // edit

    public function testItShould_showExerciseEditPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('GET', '/exercises/' . $exercise->id . '/edit');

        $this->assertResponseOk();
        $this->assertEquals($exercise->id, $this->view()->exercise->id);
    }

    public function testItShould_notShowExerciseEditPage_unauthorized()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('GET', '/exercises/' . $exercise->id . '/edit');

        $this->assertUnauthorized();
    }

    public function testItShould_notShowExerciseEditPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('GET', '/exercises/' . $exercise->id . '/edit');

        $this->assertForbidden();
    }

    public function testItShould_notShowExerciseEditPage_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/exercises/-1/edit');

        $this->assertNotFound();
    }

    // update

    public function testItShould_updateExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/exercises/' . $exercise->id, $parameters);

        /** @var Exercise $exercise */
        $exercise = $exercise->fresh();
        $this->assertEquals($parameters['question'], $exercise->question);
        $this->assertEquals($parameters['answer'], $exercise->answer);
        $this->assertRedirectedTo('/lessons/' . $lesson->id);
    }

    public function testItShould_notUpdateExercise_unauthorized()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/exercises/' . $exercise->id, $parameters);

        $this->assertUnauthorized();
    }

    public function testItShould_notUpdateExercise_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/exercises/' . $exercise->id, $parameters);

        $this->assertForbidden();
    }

    public function testItShould_notUpdateExercise_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/exercises/-1', $parameters);

        $this->assertNotFound();
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('PUT', '/exercises/' . $exercise->id);

        $this->assertInvalidInput();
    }

    // delete

    public function testItShould_deleteExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('DELETE', '/exercises/' . $exercise->id);

        $this->assertNull($exercise->fresh());
        $this->assertRedirectedTo('/lessons/' . $lesson->id);
    }

    public function testItShould_notDeleteExercise_unauthorized()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('DELETE', '/exercises/' . $exercise->id);

        $this->assertUnauthorized();
    }

    public function testItShould_notDeleteExercise_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('DELETE', '/exercises/' . $exercise->id);

        $this->assertForbidden();
    }

    public function testItShould_notDeleteExercise_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('DELETE', '/exercises/-1');

        $this->assertNotFound();
    }
}
