<?php

namespace Tests\Http\Controllers\Web;

class ExerciseSearchControllerTest extends BaseTestCase
{
    // searchForExercises

    /** @test */
    public function itShould_searchForExercises_searchPhraseSameAsQuestion()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $this->assertResponseOk();
        $viewData = $this->view()->getData();

        $this->assertEquals($viewData['exercises'][0]->id, $exercise->id);
        $this->assertEquals(0, $viewData['exercises'][0]->percent_of_good_answers);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_searchPhraseSameAsQuestion_checkPercentOfGoodAnswers()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 66,
        ]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $this->assertResponseOk();
        $viewData = $this->view()->getData();

        $this->assertEquals($viewData['exercises'][0]->id, $exercise->id);
        $this->assertEquals(66, $viewData['exercises'][0]->percent_of_good_answers);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_searchPhraseContainedInQuestion()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $question = uniqid().$phrase.uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'question' => $question]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $this->assertResponseOk();
        $viewData = $this->view()->getData();

        $this->assertEquals($viewData['exercises'][0]->id, $exercise->id);
        $this->assertEquals(0, $viewData['exercises'][0]->percent_of_good_answers);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_searchPhraseSameAsAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'answer' => $phrase]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $this->assertResponseOk();
        $viewData = $this->view()->getData();

        $this->assertEquals($viewData['exercises'][0]->id, $exercise->id);
        $this->assertEquals(0, $viewData['exercises'][0]->percent_of_good_answers);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_searchPhraseContainedInAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $answer = uniqid().$phrase.uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'answer' => $answer]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $this->assertResponseOk();
        $viewData = $this->view()->getData();

        $this->assertEquals($viewData['exercises'][0]->id, $exercise->id);
        $this->assertEquals(0, $viewData['exercises'][0]->percent_of_good_answers);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_noExercisesFound()
    {
        $this->be($user = $this->createUser());

        $phrase = uniqid();

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $this->assertResponseOk();
        $viewData = $this->view()->getData();

        $this->assertEmpty($viewData['exercises']);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_doNotSearchForExercisesOfOtherUsers()
    {
        $this->be($user = $this->createUser());

        $phrase = uniqid();

        // lesson of another user
        $lesson = $this->createPrivateLesson();
        $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $this->assertResponseOk();
        $viewData = $this->view()->getData();

        $this->assertEmpty($viewData['exercises']);
        $this->assertEquals($viewData['phrase'], $phrase);
    }

    /** @test */
    public function itShould_searchForExercises_noResultsForEmptyPhrase()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = '';

        $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $this->assertResponseOk();
        $viewData = $this->view()->getData();

        $this->assertEmpty($viewData['exercises']);
    }
}
