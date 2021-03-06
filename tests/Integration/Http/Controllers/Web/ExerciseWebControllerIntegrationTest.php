<?php

namespace Tests\Integration\Http\Controllers\Web;

use App\Events\ExerciseCreated;
use App\Models\Exercise;
use WebTestCase;

class ExerciseWebControllerIntegrationTest extends WebTestCase
{
    // create

    /** @test */
    public function itShould_showExerciseCreatePage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->responseView()->userLesson->lesson_id);
    }

    /** @test */
    public function itShould_notShowExerciseCreatePage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notShowExerciseCreatePage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create');

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notShowExerciseCreatePage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/exercises/create');

        $this->assertResponseForbidden();
    }

    // store

    /** @test */
    public function itShould_storeExercise()
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
        $this->assertResponseRedirectedTo('/lessons/' . $lesson->id . '/exercises');
    }

    /** @test */
    public function itShould_notStoreExercise_unauthorized()
    {
        $this->call('POST', '/lessons/1/exercises');
        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notStoreExercise_forbidden_userCanNotModifyLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises');

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notStoreExercise_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('POST', '/lessons/-1/exercises', $parameters);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notStoreExercise_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises');

        $this->assertResponseInvalidInput();
    }

    // createMany

    /** @test */
    public function itShould_showExerciseCreateManyPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create-many');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->responseView()->userLesson->lesson_id);
    }

    /** @test */
    public function itShould_notShowExerciseCreateManyPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create-many');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notShowExerciseCreateManyPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/exercises/create-many');

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notShowExerciseCreateManyPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/exercises/create-many');

        $this->assertResponseForbidden();
    }

    // storeMany

    /** @test */
    public function itShould_storeManyExercises()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $parameters = [
            'exercises' => "one - two\r\nthree - four"
        ];

        $this->expectsEvents(ExerciseCreated::class);

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises-many', $parameters);

        $this->assertResponseRedirectedTo('/lessons/' . $lesson->id . '/exercises');

        $exercises = Exercise::query()->get();
        $this->assertCount(2, $exercises);

        $exercise1 = $exercises->pop();
        $this->assertEquals("three", $exercise1->question);
        $this->assertEquals("four", $exercise1->answer);

        $exercise2 = $exercises->pop();
        $this->assertEquals("one", $exercise2->question);
        $this->assertEquals("two", $exercise2->answer);
    }

    /** @test */
    public function itShould_storeManyExercises_ignoreLinesWithoutDelimiter()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $parameters = [
            'exercises' => "one - two\r\nthree four"
        ];

        $this->expectsEvents(ExerciseCreated::class);

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises-many', $parameters);

        $this->assertResponseRedirectedTo('/lessons/' . $lesson->id . '/exercises');

        $exercises = Exercise::query()->get();
        $this->assertCount(1, $exercises);

        $exercise = $exercises->pop();
        $this->assertEquals("one", $exercise->question);
        $this->assertEquals("two", $exercise->answer);
    }

    /** @test */
    public function itShould_notStoreManyExercises_unauthorized()
    {
        $this->call('POST', '/lessons/1/exercises-many');
        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notStoreManyExercises_forbidden_userCanNotModifyLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises-many');

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notStoreManyExercises_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('POST', '/lessons/-1/exercises-many', $parameters);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notStoreManyExercises_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('POST', '/lessons/' . $lesson->id . '/exercises-many');

        $this->assertResponseInvalidInput();
    }

    // edit

    /** @test */
    public function itShould_showExerciseEditPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $redirectTo = $this->randomUrl();

        $this->call('GET', '/exercises/' . $exercise->id . '/edit?redirect_to=' . urlencode($redirectTo));

        $this->assertResponseOk();
        $this->assertEquals($exercise->id, $this->responseView()->exercise->id);
        $this->assertEquals($redirectTo, $this->responseView()->redirectTo);
    }

    /**
     * @test
     * @dataProvider trueFalseProvider
     * @param bool $hideLesson
     */
    public function itShould_showExerciseEditPage_hideLesson(bool $hideLesson)
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('GET', '/exercises/' . $exercise->id . '/edit?hide_lesson=' . $hideLesson);

        $this->assertResponseOk();
        $this->assertEquals($exercise->id, $this->responseView()->exercise->id);
        $this->assertEquals($hideLesson, $this->responseView()->hideLesson);
    }

    /** @test */
    public function itShould_showExerciseEditPage_defaultToPreviousPageIfRedirectUrlNotDefined()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $redirectTo = $this->randomUrl();

        $this->call(
            'GET',
            '/exercises/' . $exercise->id . '/edit',
            $parameters = [],
            $cookies = [],
            $files = [],
            $server = [
                'HTTP_REFERER' => $redirectTo,
            ]
        );

        $this->assertResponseOk();
        $this->assertEquals($exercise->id, $this->responseView()->exercise->id);
        $this->assertEquals($redirectTo, $this->responseView()->redirectTo);
    }

    /** @test */
    public function itShould_notShowExerciseEditPage_unauthorized()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('GET', '/exercises/' . $exercise->id . '/edit');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notShowExerciseEditPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('GET', '/exercises/' . $exercise->id . '/edit');

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notShowExerciseEditPage_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/exercises/-1/edit');

        $this->assertResponseNotFound();
    }

    // update

    /** @test */
    public function itShould_updateExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $redirectTo = $this->randomUrl();

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
            'redirect_to' => $redirectTo,
        ];

        $this->call('PUT', '/exercises/' . $exercise->id, $parameters);

        /** @var Exercise $exercise */
        $exercise = $exercise->fresh();
        $this->assertEquals($parameters['question'], $exercise->question);
        $this->assertEquals($parameters['answer'], $exercise->answer);
        $this->assertResponseRedirectedTo($redirectTo);
    }

    /** @test */
    public function itShould_notUpdateExercise_unauthorized()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/exercises/' . $exercise->id, $parameters);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notUpdateExercise_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/exercises/' . $exercise->id, $parameters);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notUpdateExercise_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/exercises/-1', $parameters);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notUpdateExercise_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('PUT', '/exercises/' . $exercise->id);

        $this->assertResponseInvalidInput();
    }

    // delete

    /** @test */
    public function itShould_deleteExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $redirectTo = $this->randomUrl();

        $this->call(
            'DELETE',
            '/exercises/' . $exercise->id,
            $parameters = [],
            $cookies = [],
            $files = [],
            $server = [
                'HTTP_REFERER' => $redirectTo,
            ]
        );

        $this->assertNull($exercise->fresh());
        $this->assertResponseRedirectedTo($redirectTo);
    }

    /** @test */
    public function itShould_deleteExercise_ensurePercentOfGoodAnswersOfLessonIsRecalculated()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $redirectTo = $this->randomUrl();

        // pre set percent_of_good_answers to some value different than 0,
        // because 0 should be the result percent_of_good_answers after only exercise is deleted
        $lesson->subscribedUsers[0]->pivot->percent_of_good_answers = 20;
        $lesson->subscribedUsers[0]->pivot->save();
        $this->assertEquals(20, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));

        $this->call(
            'DELETE',
            '/exercises/' . $exercise->id,
            $parameters = [],
            $cookies = [],
            $files = [],
            $server = [
                'HTTP_REFERER' => $redirectTo,
            ]
        );

        $this->assertNull($exercise->fresh());
        $this->assertResponseRedirectedTo($redirectTo);

        // just one exercise without answers = 0% of good answers
        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_notDeleteExercise_unauthorized()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('DELETE', '/exercises/' . $exercise->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notDeleteExercise_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('DELETE', '/exercises/' . $exercise->id);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notDeleteExercise_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('DELETE', '/exercises/-1');

        $this->assertResponseNotFound();
    }
}
