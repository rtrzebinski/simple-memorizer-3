<?php

namespace Tests\Http\Controllers\Api;

use App\Events\ExerciseDeleted;
use App\Models\Exercise;

class ExerciseControllerTest extends BaseTestCase
{
    // storeExercise

    /** @test */
    public function itShould_storeExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises', $input, $user);

        $this->assertResponseOk();

        $this->seeJsonFragment([
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

    /** @test */
    public function itShould_storeExercise_ensurePercentOfGoodAnswersOfLessonIsRecalculated()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        // pre set percent_of_good_answers to some value different than 0,
        // because 0 should be the result percent_of_good_answers after first exercise is stored
        $lesson->subscribedUsers[0]->pivot->percent_of_good_answers = 20;
        $lesson->subscribedUsers[0]->pivot->save();
        $this->assertEquals(20, $lesson->percentOfGoodAnswers($user->id));

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises', $input, $user);

        $this->assertResponseOk();

        $this->seeJsonFragment([
            'question' => $input['question'],
            'answer' => $input['answer'],
            'lesson_id' => $lesson->id,
        ]);

        /** @var Exercise $exercise */
        $exercise = $this->last(Exercise::class);
        $this->assertEquals($input['question'], $exercise->question);
        $this->assertEquals($input['answer'], $exercise->answer);
        $this->assertEquals($lesson->id, $exercise->lesson_id);

        // just one exercise without answers = 0% of good answers
        $this->assertEquals(0, $lesson->percentOfGoodAnswers($user->id));
    }

    /** @test */
    public function itShould_notStoreExercise_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notStoreExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises', $input = [], $user);

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notStoreExercise_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('POST', '/lessons/'.$lesson->id.'/exercises', $input, $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notStoreExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/lessons/-1/exercises', $input = [], $user);

        $this->assertResponseNotFound();
    }

    // fetchExercise

    /** @test */
    public function itShould_fetchExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertResponseOk();
        $this->seeJsonFragment($exercise->toArray());
    }

    /** @test */
    public function itShould_notFetchExercise_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('GET', '/exercises/'.$exercise->id);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notFetchExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('GET', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notFetchExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/exercises/-1', $input = [], $user);

        $this->assertResponseNotFound();
    }

    // fetchExercisesOfLesson

    /** @test */
    public function itShould_fetchExercisesOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises', $input = [], $user);

        $this->assertResponseOk();
        $this->seeJsonFragment([$exercise->toArray()]);
    }

    /** @test */
    public function itShould_notFetchExercisesOfLesson_unauthorised()
    {
        $lesson = $this->createLesson();

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notFetchExercisesOfLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises', $input = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notFetchExercisesOfLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises', $input = [], $user);

        $this->assertResponseNotFound();
    }

    // updateExercise

    /** @test */
    public function itShould_updateExercise()
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

        $this->seeJsonFragment([
            'question' => $input['question'],
            'answer' => $input['answer'],
        ]);

        /** @var Exercise $exercise */
        $exercise = $exercise->fresh();
        $this->assertEquals($input['question'], $exercise->question);
        $this->assertEquals($input['answer'], $exercise->answer);
    }

    /** @test */
    public function itShould_notUpdateExercise_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('PATCH', '/exercises/'.$exercise->id);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notUpdateExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('PATCH', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notUpdateExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->callApi('PATCH', '/exercises/'.$exercise->id, $input, $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notUpdateExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('PATCH', '/exercises/-1', $input = [], $user);

        $this->assertResponseNotFound();
    }

    // deleteExercise

    /** @test */
    public function itShould_deleteExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->expectsEvents(ExerciseDeleted::class);

        $this->callApi('DELETE', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertResponseOk();
        $this->assertNull($exercise->fresh());
    }

    /** @test */
    public function itShould_deleteExercise_ensurePercentOfGoodAnswersOfLessonIsRecalculated()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        // pre set percent_of_good_answers to some value different than 0,
        // because 0 should be the result percent_of_good_answers after only exercise is deleted
        $lesson->subscribedUsers[0]->pivot->percent_of_good_answers = 20;
        $lesson->subscribedUsers[0]->pivot->save();
        $this->assertEquals(20, $lesson->percentOfGoodAnswers($user->id));

        $this->callApi('DELETE', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertResponseOk();
        $this->assertNull($exercise->fresh());

        // just one exercise without answers = 0% of good answers
        $this->assertEquals(0, $lesson->fresh()->percentOfGoodAnswers($user->id));
    }

    /** @test */
    public function itShould_notDeleteExercise_unauthorised()
    {
        $exercise = $this->createExercise();

        $this->callApi('DELETE', '/exercises/'.$exercise->id);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notDeleteExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('DELETE', '/exercises/'.$exercise->id, $input = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notDeleteExercise_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', '/exercises/-1', $input = [], $user);

        $this->assertResponseNotFound();
    }
}
