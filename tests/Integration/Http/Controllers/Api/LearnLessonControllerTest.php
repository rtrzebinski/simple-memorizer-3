<?php

namespace Tests\Integration\Http\Controllers\Api;

use ApiTestCase;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;

class LearnLessonControllerTest extends ApiTestCase
{
    // fetchRandomExerciseOfLesson

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random', $data = [], $user);

        $this->assertResponseOk();
        $response = $this->response->decodeResponseJson();
        $this->assertIsInt($response['exercise_id']);
        $this->assertIsInt($response['lesson_id']);
        $this->assertIsString($response['lesson_name']);
        $this->assertTrue(strlen($response['lesson_name']) > 0);
        $this->assertIsString($response['question']);
        $this->assertTrue(strlen($response['question']) > 0);
        $this->assertIsString($response['answer']);
        $this->assertTrue(strlen($response['answer']) > 0);
        $this->assertSame(0, $response['number_of_good_answers']);
        $this->assertSame(0, $response['number_of_good_answers_today']);
        $this->assertNull($response['latest_good_answer']);
        $this->assertSame(0, $response['number_of_bad_answers']);
        $this->assertSame(0, $response['number_of_bad_answers_today']);
        $this->assertNull($response['latest_bad_answer']);
        $this->assertSame(0, $response['percent_of_good_answers']);
    }

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi(
            'GET',
            '/lessons/' . $lesson->id . '/exercises/random',
            ['previous_exercise_id' => $previous->id],
            $user
        );

        $this->assertResponseOk();
        $response = $this->response->decodeResponseJson();

        $this->assertIsInt($response['exercise_id']);
        $this->assertIsInt($response['lesson_id']);
        $this->assertIsString($response['lesson_name']);
        $this->assertTrue(strlen($response['lesson_name']) > 0);
        $this->assertIsString($response['question']);
        $this->assertTrue(strlen($response['question']) > 0);
        $this->assertIsString($response['answer']);
        $this->assertTrue(strlen($response['answer']) > 0);
        $this->assertSame(0, $response['number_of_good_answers']);
        $this->assertSame(0, $response['number_of_good_answers_today']);
        $this->assertNull($response['latest_good_answer']);
        $this->assertSame(0, $response['number_of_bad_answers']);
        $this->assertSame(0, $response['number_of_bad_answers_today']);
        $this->assertNull($response['latest_bad_answer']);
        $this->assertSame(0, $response['percent_of_good_answers']);
        $this->assertTrue($response['exercise_id'] != $previous->id);
    }

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson_lessonIsBidirectional()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $this->updateBidirectional($lesson, $user->id, $bidirectional = true);

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random', $data = [], $user);

        $response = $this->response->decodeResponseJson();
        $this->assertResponseOk();
        $this->assertIsInt($response['exercise_id']);
        $this->assertIsInt($response['lesson_id']);
        $this->assertIsString($response['lesson_name']);
        $this->assertTrue(strlen($response['lesson_name']) > 0);
        $this->assertIsString($response['question']);
        $this->assertTrue(strlen($response['question']) > 0);
        $this->assertIsString($response['answer']);
        $this->assertTrue(strlen($response['answer']) > 0);
        $this->assertSame(0, $response['number_of_good_answers']);
        $this->assertSame(0, $response['number_of_good_answers_today']);
        $this->assertNull($response['latest_good_answer']);
        $this->assertSame(0, $response['number_of_bad_answers']);
        $this->assertSame(0, $response['number_of_bad_answers_today']);
        $this->assertNull($response['latest_bad_answer']);
        $this->assertSame(0, $response['percent_of_good_answers']);
    }

    /** @test */
    public function itShould_notFindUserExerciseToLearn_unauthorized()
    {
        $this->callApi('GET', '/lessons/' . $this->createLesson()->id . '/exercises/random', $data = []);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notFindUserExerciseToLearn_forbidden()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/' . $this->createPrivateLesson()->id . '/exercises/random', $data = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notFindUserExerciseToLearn_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises/random', $data = [], $user);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notFindUserExerciseToLearn_invalidPreviousExerciseId()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();

        $this->callApi(
            'GET',
            '/lessons/' . $lesson->id . '/exercises/random',
            ['previous_exercise_id' => -1],
            $user
        );

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notFindUserExerciseToLearn_noExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')
            ->with($lesson->id)->willReturn(collect([]));

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random', [], $user);

        $this->assertResponseOk();
        $this->seeJson([]);
    }

    /** @test */
    public function itShould_notFindUserExerciseToLearn_noExercises_lessonBidirectional()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = true);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')
            ->with($lesson->id)->willReturn(collect([]));

        /** @var AuthenticatedUserLessonRepositoryInterface|MockObject $userLessonRepository */
        $userLessonRepository = $this->createMock(AuthenticatedUserLessonRepositoryInterface::class);
        $this->instance(AuthenticatedUserLessonRepositoryInterface::class, $userLessonRepository);
        $userLessonRepository->expects($this->once())->method('fetchUserLesson')->with($lesson->id)
            ->willReturn($userLesson);

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises/random', [], $user);

        $this->assertResponseOk();
        $this->seeJson([]);
    }

    // handleGoodAnswer

    /** @test */
    public function itShould_handleGoodAnswer()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-good-answer', $data = [], $user);

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
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-good-answer');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi(
            'POST',
            '/exercises/-1/handle-good-answer',
            $data =
                [],
            $user
        );

        $this->assertResponseNotFound();
    }

    // handleBadAnswer

    /** @test */
    public function itShould_handleBadAnswer()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-bad-answer', $data = [], $user);

        $this->assertResponseOk();

        $this->assertEquals(1, $this->numberOfBadAnswers($exercise, $user->id));
        $this->assertEquals(0, $this->percentOfGoodAnswersOfExercise($exercise, $user->id));
        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_notHandleBadAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/' . $exercise->id . '/handle-bad-answer');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notHandleBadAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi(
            'POST',
            '/exercises/-1/handle-bad-answer',
            $data =
                [],
            $user
        );

        $this->assertResponseNotFound();
    }
}
