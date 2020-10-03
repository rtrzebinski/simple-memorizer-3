<?php

namespace Tests\Unit\Http\Controllers\Web;

use WebTestCase;

class ExerciseSearchControllerTest extends WebTestCase
{
    // searchForExercises

    /** @test */
    public function itShould_searchForExercises_searchPhraseSameAsQuestion()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);

        $this->call('GET', '/exercises/search?phrase=' . $phrase);

        $this->assertResponseOk();
        $viewData = $this->responseView()->getData();

        $this->assertEquals($viewData['userExercises'][0]->exercise_id, $exercise->id);
        $this->assertEquals(0, $viewData['userExercises'][0]->percent_of_good_answers);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_noExercisesFound()
    {
        $this->be($user = $this->createUser());

        $phrase = uniqid();

        $this->call('GET', '/exercises/search?phrase=' . $phrase);

        $this->assertResponseOk();
        $viewData = $this->responseView()->getData();

        $this->assertEmpty($viewData['userExercises']);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_noResultsForEmptyPhrase()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = '';

        $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);

        $this->call('GET', '/exercises/search?phrase=' . $phrase);

        $this->assertResponseOk();
        $viewData = $this->responseView()->getData();

        $this->assertEmpty($viewData['userExercises']);
    }

    /** @test */
    public function itShould_notSearchForExercises_unauthorized()
    {
        $this->call('GET', '/exercises/search');

        $this->assertResponseUnauthorized();
    }
}
