<?php

namespace Tests\Http\Controllers\Web;

class ExerciseControllerTest extends BaseTestCase
{
    public function testItShould_showExerciseCreatePage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create');

        $this->assertResponseOk();
        $this->assertViewHas('lesson');
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

    public function testItShould_showExerciseEditPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('GET', '/exercises/' . $exercise->id . '/edit');

        $this->assertResponseOk();
        $this->assertViewHas('exercise');
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
}
