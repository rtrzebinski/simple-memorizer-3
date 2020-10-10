<?php

namespace Tests\Integration\Http\Controllers\Web;

use App\Services\LearningService;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\UserExercise\UserExercise;
use App\Structures\UserLesson\UserLesson;
use PHPUnit\Framework\MockObject\MockObject;
use WebTestCase;

class LearnLessonControllerTest extends WebTestCase
{
    // learnLesson

    /** @test */
    public function itShould_showLessonLearnPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertResponseOk();

        /** @var UserLesson $userLesson */
        $userLesson = $this->responseView()->getData()['userLesson'];
        $this->assertEquals($user->id, $userLesson->user_id);
        $this->assertEquals($lesson->id, $userLesson->lesson_id);
        $this->assertEquals($lesson->owner_id, $userLesson->owner_id);
        $this->assertEquals($lesson->name, $userLesson->name);
        $this->assertEquals(false, $userLesson->is_bidirectional);
        $this->assertEquals('public', $userLesson->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $userLesson->exercises_count);
        $this->assertEquals(0, $userLesson->percent_of_good_answers);
        $this->assertEquals(1, $userLesson->subscribers_count);
        $this->assertEquals(0, $userLesson->child_lessons_count);
        $this->assertEquals(true, $userLesson->is_subscriber);

        $canModifyExercise = $this->responseView()->getData()['canEditExercise'];
        $this->assertTrue($canModifyExercise);

        /** @var UserExercise $userExercise */
        $userExercise = $this->responseView()->getData()['userExercise'];

        $editExerciseUrl = $this->responseView()->getData()['editExerciseUrl'];
        $this->assertEquals(
            'http://localhost/exercises/' . $userExercise->exercise_id . '/edit?hide_lesson=true&redirect_to=%2Flearn%2Flessons%2F1%3Frequested_exercise_id%3D' . $userExercise->exercise_id,
            $editExerciseUrl
        );

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
    public function itShould_showLessonLearnPage_withPreviousExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $lesson->subscribe($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $previous = $lesson->exercises[0];

        $this->call('GET', '/learn/lessons/' . $lesson->id . '?previous_exercise_id=' . $previous->id);

        $this->assertResponseOk();

        /** @var UserLesson $userLesson */
        $userLesson = $this->responseView()->getData()['userLesson'];
        $this->assertEquals($user->id, $userLesson->user_id);
        $this->assertEquals($lesson->id, $userLesson->lesson_id);
        $this->assertEquals($lesson->owner_id, $userLesson->owner_id);
        $this->assertEquals($lesson->name, $userLesson->name);
        $this->assertEquals(false, $userLesson->is_bidirectional);
        $this->assertEquals('public', $userLesson->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $userLesson->exercises_count);
        $this->assertEquals(0, $userLesson->percent_of_good_answers);
        $this->assertEquals(1, $userLesson->subscribers_count);
        $this->assertEquals(0, $userLesson->child_lessons_count);
        $this->assertEquals(true, $userLesson->is_subscriber);

        $canModifyExercise = $this->responseView()->getData()['canEditExercise'];
        $this->assertFalse($canModifyExercise);

        /** @var UserExercise $userExercise */
        $userExercise = $this->responseView()->getData()['userExercise'];

        $editExerciseUrl = $this->responseView()->getData()['editExerciseUrl'];
        $this->assertEquals(
            'http://localhost/exercises/' . $userExercise->exercise_id . '/edit?hide_lesson=true&redirect_to=%2Flearn%2Flessons%2F1%3Frequested_exercise_id%3D' . $userExercise->exercise_id,
            $editExerciseUrl
        );

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
    public function itShould_showLessonLearnPage_withRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $lesson->exercises[0];

        $this->call('GET', '/learn/lessons/' . $lesson->id . '?requested_exercise_id=' . $requested->id);

        /** @var UserLesson $userLesson */
        $userLesson = $this->responseView()->getData()['userLesson'];
        $this->assertEquals($user->id, $userLesson->user_id);
        $this->assertEquals($lesson->id, $userLesson->lesson_id);
        $this->assertEquals($lesson->owner_id, $userLesson->owner_id);
        $this->assertEquals($lesson->name, $userLesson->name);
        $this->assertEquals(false, $userLesson->is_bidirectional);
        $this->assertEquals('private', $userLesson->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $userLesson->exercises_count);
        $this->assertEquals(0, $userLesson->percent_of_good_answers);
        $this->assertEquals(1, $userLesson->subscribers_count);
        $this->assertEquals(0, $userLesson->child_lessons_count);
        $this->assertEquals(true, $userLesson->is_subscriber);

        $canModifyExercise = $this->responseView()->getData()['canEditExercise'];
        $this->assertTrue($canModifyExercise);

        /** @var UserExercise $userExercise */
        $userExercise = $this->responseView()->getData()['userExercise'];

        $editExerciseUrl = $this->responseView()->getData()['editExerciseUrl'];
        $this->assertEquals(
            'http://localhost/exercises/' . $userExercise->exercise_id . '/edit?hide_lesson=true&redirect_to=%2Flearn%2Flessons%2F1%3Frequested_exercise_id%3D' . $userExercise->exercise_id,
            $editExerciseUrl
        );

        $this->assertIsInt($userExercise->exercise_id);
        $this->assertIsInt($userExercise->lesson_id);
        $this->assertEquals($lesson->name, $userExercise->lesson_name);
        $this->assertEquals($lesson->owner_id, $userExercise->lesson_owner_id);
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
    public function itShould_fetchRandomExerciseOfLesson_lessonIsBidirectional()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->updateBidirectional($lesson, $user->id, $bidirectional = true);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertResponseOk();

        /** @var UserLesson $userLesson */
        $userLesson = $this->responseView()->getData()['userLesson'];
        $this->assertEquals($user->id, $userLesson->user_id);
        $this->assertEquals($lesson->id, $userLesson->lesson_id);
        $this->assertEquals($lesson->owner_id, $userLesson->owner_id);
        $this->assertEquals($lesson->name, $userLesson->name);
        $this->assertEquals($bidirectional, $userLesson->is_bidirectional);
        $this->assertEquals('public', $userLesson->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $userLesson->exercises_count);
        $this->assertEquals(0, $userLesson->percent_of_good_answers);
        $this->assertEquals(1, $userLesson->subscribers_count);
        $this->assertEquals(0, $userLesson->child_lessons_count);
        $this->assertEquals(true, $userLesson->is_subscriber);
        $canModifyExercise = $this->responseView()->getData()['canEditExercise'];
        $this->assertTrue($canModifyExercise);

        /** @var UserExercise $userExercise */
        $userExercise = $this->responseView()->getData()['userExercise'];

        $editExerciseUrl = $this->responseView()->getData()['editExerciseUrl'];
        $this->assertEquals(
            'http://localhost/exercises/' . $userExercise->exercise_id . '/edit?hide_lesson=true&redirect_to=%2Flearn%2Flessons%2F1%3Frequested_exercise_id%3D' . $userExercise->exercise_id,
            $editExerciseUrl
        );

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
    public function itShould_notShowLessonLearnPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notShowLessonLearnPage_forbiddenToAccessRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $this->createExercise();

        $this->call('GET', '/learn/lessons/' . $lesson->id . '?requested_exercise_id=' . $requested->id);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notShowLessonLearnPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/learn/lessons/-1');

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notShowLessonLearnPage_userDoesNotSubscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertResponseForbidden();
    }

    // handleGoodAnswer

    /** @test */
    public function itShould_handleGoodAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();
        $this->createUserExercise($user, $exercise);

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'good'
        ];

        $this->call('POST', '/learn/lessons/' . $lesson->id, $data);

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

        $this->call('POST', '/learn/lessons/' . $lesson->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/lessons/' . $lesson->id);

        $this->assertResponseInvalidInput();
    }

    // handleBadAnswer

    /** @test */
    public function itShould_handleBadAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();
        $this->createUserExercise($user, $exercise);

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'bad'
        ];

        $this->call('POST', '/learn/lessons/' . $lesson->id, $data);

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

        $this->call('POST', '/learn/lessons/' . $lesson->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notHandleBadAnswer_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/lessons/' . $lesson->id);

        $this->assertResponseInvalidInput();
    }
}
