<?php

namespace Tests\Unit\Http\Controllers\Api;

use ApiTestCase;
use App\Services\LearningService;
use App\Services\UserExerciseModifier;
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
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise = $this->createExercise();

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')
            ->with($lesson->id)->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);
        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises, $previous->id)
            ->willReturn($userExercise);

        /** @var AuthenticatedUserLessonRepositoryInterface|MockObject $userLessonRepository */
        $userLessonRepository = $this->createMock(AuthenticatedUserLessonRepositoryInterface::class);
        $this->instance(AuthenticatedUserLessonRepositoryInterface::class, $userLessonRepository);
        $userLessonRepository->expects($this->once())->method('fetchUserLesson')->with($lesson->id)
            ->willReturn($userLesson);

        /** @var UserExerciseModifier|MockObject $userExerciseModifier */
        $userExerciseModifier = $this->createMock(UserExerciseModifier::class);
        $this->instance(UserExerciseModifier::class, $userExerciseModifier);
        $userExerciseModifier->expects($this->never())->method('swapQuestionWithAnswer');

        $this->callApi(
            'GET',
            '/lessons/' . $lesson->id . '/exercises/random',
            ['previous_exercise_id' => $previous->id],
            $user
        );

        $this->assertResponseOk();
        $this->seeJsonFragment((array)$userExercise);
    }

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson_lessonIsBidirectional()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise = $this->createExercise();

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = true);
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')
            ->with($lesson->id)->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);
        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises, $previous->id)
            ->willReturn($userExercise);

        /** @var AuthenticatedUserLessonRepositoryInterface|MockObject $userLessonRepository */
        $userLessonRepository = $this->createMock(AuthenticatedUserLessonRepositoryInterface::class);
        $this->instance(AuthenticatedUserLessonRepositoryInterface::class, $userLessonRepository);
        $userLessonRepository->expects($this->once())->method('fetchUserLesson')->with($lesson->id)
            ->willReturn($userLesson);

        /** @var UserExerciseModifier|MockObject $userExerciseModifier */
        $userExerciseModifier = $this->createMock(UserExerciseModifier::class);
        $this->instance(UserExerciseModifier::class, $userExerciseModifier);
        $userExerciseModifier->expects($this->once())->method('swapQuestionWithAnswer')
            ->with($userExercise)->willReturn($userExercise);

        $this->callApi(
            'GET',
            '/lessons/' . $lesson->id . '/exercises/random',
            ['previous_exercise_id' => $previous->id],
            $user
        );

        $this->assertResponseOk();
        $this->seeJsonFragment((array)$userExercise);
    }
}
