<?php

namespace Tests\Http\Controllers\Api;

use App\Exceptions\NotEnoughExercisesException;
use App\Models\Lesson;
use App\Services\LearningService;
use PHPUnit\Framework\MockObject\MockObject;

class LearningControllerTest extends BaseTestCase
{

    // fetchRandomExerciseOfLesson

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise = $this->createExercise();

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('fetchRandomExerciseOfLesson')
            ->with($this->isInstanceOf(Lesson::class), $user->id, $previous->id)
            ->willReturn($exercise);

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random',
            ['previous_exercise_id' => $previous->id], $user);

        $this->assertResponseOk();
        $this->seeJsonFragment($exercise->toArray());
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_unauthorized()
    {
        $this->callApi('GET', '/lessons/'.$this->createLesson()->id.'/exercises/random', $data = []);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_forbidden()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/'.$this->createPrivateLesson()->id.'/exercises/random', $data = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1/exercises/random', $data = [], $user);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_invalidPreviousExerciseId()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id])->fresh();

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random',
            ['previous_exercise_id' => -1], $user);

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notFetchRandomExerciseOfLesson_notEnoughExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('fetchRandomExerciseOfLesson')
            ->with($this->isInstanceOf(Lesson::class), $user->id)
            ->willThrowException(new NotEnoughExercisesException());

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random', [], $user);

        $this->assertResponseStatus(NotEnoughExercisesException::HTTP_RESPONSE_CODE);
    }

    // handleGoodAnswer

    /** @test */
    public function itShould_handleGoodAnswer()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-good-answer', $data = [], $user);

        $this->assertResponseOk();

        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(100, $exercise->percentOfGoodAnswersOfUser($user->id));
        $this->assertEquals(50, $lesson->percentOfGoodAnswersOfUser($user->id));
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-good-answer');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-good-answer', $data =
            [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/handle-good-answer', $data =
            [], $user);

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

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-bad-answer', $data = [], $user);

        $this->assertResponseOk();

        $this->assertEquals(1, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
        $this->assertEquals(0, $lesson->percentOfGoodAnswersOfUser($user->id));
    }

    /** @test */
    public function itShould_notHandleBadAnswer_unauthorized()
    {
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-bad-answer');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notHandleBadAnswer_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->callApi('POST', '/exercises/'.$exercise->id.'/handle-bad-answer', $data =
            [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notHandleBadAnswer_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/exercises/-1/handle-bad-answer', $data =
            [], $user);

        $this->assertResponseNotFound();
    }
}
