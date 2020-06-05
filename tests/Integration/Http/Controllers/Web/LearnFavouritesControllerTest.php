<?php

namespace Tests\Integration\Http\Controllers\Web;

use App\Structures\UserExercise\UserExercise;
use WebTestCase;

class LearnFavouritesControllerTest extends WebTestCase
{
    // learnFavourites

    /** @test */
    public function itShould_showLearnFavouritesPage()
    {
        $this->be($user = $this->createUser());

        // create a lesson and subscribe it
        $lesson = $this->createPublicLesson();
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $lesson->subscribe($user);
        $this->updateFavourite($lesson, $user->id, $favourite = true);

        $this->call('GET', '/learn/favourites');

        $this->assertResponseOk();

        $this->assertFalse(isset($this->view()->getData()['userLesson']));

        $canModifyExercise = $this->view()->getData()['canModifyExercise'];
        $this->assertFalse($canModifyExercise);

        /** @var UserExercise $userExercise */
        $userExercise = $this->view()->getData()['userExercise'];
        $this->assertInstanceOf(UserExercise::class, $userExercise);
        $this->assertIsInt($userExercise->exercise_id);
        $this->assertIsInt($userExercise->lesson_id);
        $this->assertIsString($userExercise->lesson_name);
        $this->assertTrue(strlen($userExercise->lesson_name) > 0);
        $this->assertIsString($userExercise->question);
        $this->assertTrue(strlen($userExercise->question) > 0);
        $this->assertIsString($userExercise->answer);
        $this->assertTrue(strlen($userExercise->answer) > 0);
        $this->assertSame(0, $userExercise->number_of_good_answers);
        $this->assertSame(0, $userExercise->number_of_good_answers_today);
        $this->assertNull($userExercise->latest_good_answer);
        $this->assertSame(0, $userExercise->number_of_bad_answers);
        $this->assertSame(0, $userExercise->number_of_bad_answers_today);
        $this->assertNull($userExercise->latest_bad_answer);
        $this->assertSame(0, $userExercise->percent_of_good_answers);
    }

    /** @test */
    public function itShould_showLearnFavouritesPage_withPreviousExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $lesson->subscribe($user);
        $this->updateFavourite($lesson, $user->id, $favourite = true);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $previous = $lesson->exercises[0];

        $this->call('GET', '/learn/favourites?previous_exercise_id='.$previous->id);

        $this->assertResponseOk();

        $this->assertFalse(isset($this->view()->getData()['userLesson']));

        $canModifyExercise = $this->view()->getData()['canModifyExercise'];
        $this->assertFalse($canModifyExercise);

        /** @var UserExercise $userExercise */
        $userExercise = $this->view()->getData()['userExercise'];
        $this->assertIsInt($userExercise->exercise_id);
        $this->assertIsInt($userExercise->lesson_id);
        $this->assertIsString($userExercise->lesson_name);
        $this->assertTrue(strlen($userExercise->lesson_name) > 0);
        $this->assertIsString($userExercise->question);
        $this->assertTrue(strlen($userExercise->question) > 0);
        $this->assertIsString($userExercise->answer);
        $this->assertTrue(strlen($userExercise->answer) > 0);
        $this->assertSame(0, $userExercise->number_of_good_answers);
        $this->assertSame(0, $userExercise->number_of_good_answers_today);
        $this->assertNull($userExercise->latest_good_answer);
        $this->assertSame(0, $userExercise->number_of_bad_answers);
        $this->assertSame(0, $userExercise->number_of_bad_answers_today);
        $this->assertNull($userExercise->latest_bad_answer);
        $this->assertSame(0, $userExercise->percent_of_good_answers);
        $this->assertTrue($userExercise->exercise_id != $previous->id);
    }

    /** @test */
    public function itShould_showLearnFavouritesPage_withRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->updateFavourite($lesson, $user->id, $favourite = true);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $lesson->exercises[0];

        $this->call('GET', '/learn/favourites/?requested_exercise_id='.$requested->id);

        $this->assertFalse(isset($this->view()->getData()['userLesson']));

        $canModifyExercise = $this->view()->getData()['canModifyExercise'];
        $this->assertFalse($canModifyExercise);

        /** @var UserExercise $userExercise */
        $userExercise = $this->view()->getData()['userExercise'];
        $this->assertIsInt($userExercise->exercise_id);
        $this->assertIsInt($userExercise->lesson_id);
        $this->assertNull($userExercise->lesson_name);
        $this->assertIsString($userExercise->question);
        $this->assertTrue(strlen($userExercise->question) > 0);
        $this->assertIsString($userExercise->answer);
        $this->assertTrue(strlen($userExercise->answer) > 0);
        $this->assertSame(0, $userExercise->number_of_good_answers);
        $this->assertSame(0, $userExercise->number_of_good_answers_today);
        $this->assertNull($userExercise->latest_good_answer);
        $this->assertSame(0, $userExercise->number_of_bad_answers);
        $this->assertSame(0, $userExercise->number_of_bad_answers_today);
        $this->assertNull($userExercise->latest_bad_answer);
        $this->assertSame(0, $userExercise->percent_of_good_answers);
        $this->assertTrue($userExercise->exercise_id == $requested->id);
    }

    /** @test */
    public function itShould_notShowLearnFavouritesPage_unauthorized()
    {
        $this->call('GET', '/learn/favourites/');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notShowLearnFavouritesPage_forbiddenToAccessRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $this->createExercise();

        $this->call('GET', '/learn/favourites?requested_exercise_id='.$requested->id);

        $this->assertResponseForbidden();
    }

    // handleGoodAnswer

    /** @test */
    public function itShould_handleGoodAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->updateFavourite($lesson, $user->id, $favourite = true);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'good'
        ];

        $this->call('POST', '/learn/favourites', $data);

        $this->assertResponseOk();

        $this->assertEquals(1, $this->numberOfGoodAnswers($exercise, $user->id));
        $this->assertEquals(100, $this->percentOfGoodAnswersOfExercise($exercise, $user->id));
        // 10 because 2 exercises are required to learn a lesson,
        // so one will be 0%, another will be 100%
        // (0 + 100) / 2 = 50
        $this->assertEquals(50, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_unauthorized()
    {
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/favourites');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/favourites');

        $this->assertResponseInvalidInput();
    }

    // handleBadAnswer

    /** @test */
    public function itShould_handleBadAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->updateFavourite($lesson, $user->id, $favourite = true);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'bad'
        ];

        $this->call('POST', '/learn/favourites', $data);

        $this->assertResponseOk();

        $this->assertEquals(0, $this->numberOfGoodAnswers($exercise, $user->id));
        $this->assertEquals(0, $this->percentOfGoodAnswersOfExercise($exercise, $user->id));
        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_notHandleBadAnswer_unauthorized()
    {
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/favourites');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notHandleBadAnswer_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/favourites');

        $this->assertResponseInvalidInput();
    }

    // updateExercise

    /** @test */
    public function itShould_updateExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/learn/favourites/'.$exercise->id, $parameters);

        $this->assertResponseRedirectedTo('/learn/favourites/?requested_exercise_id='.$exercise->id);
        $exercise = $exercise->fresh();
        $this->assertEquals($parameters['question'], $exercise->question);
        $this->assertEquals($parameters['answer'], $exercise->answer);
    }

    /** @test */
    public function itShould_notUpdateExercise_unauthorized()
    {
        $lesson = $this->createPublicLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/learn/favourites/'.$exercise->id, $parameters);

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

        $this->call('PUT', '/learn/favourites/'.$exercise->id, $parameters);

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

        $this->call('PUT', '/learn/favourites/-1', $parameters);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notUpdateExercise_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('PUT', '/learn/favourites/'.$exercise->id);

        $this->assertResponseInvalidInput();
    }
}
