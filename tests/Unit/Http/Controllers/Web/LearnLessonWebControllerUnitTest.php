<?php

namespace Tests\Unit\Http\Controllers\Web;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Services\LearningService;
use App\Services\UserExerciseModifier;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use WebTestCase;

class LearnLessonWebControllerUnitTest extends WebTestCase
{
    // learn

    /** @test */
    public function itShould_showLessonLearnPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $lesson->subscribe($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $this->createExercise();
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')->with($lesson->id)
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises)
            ->willReturn($userExercise);

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->responseView()->userLesson->lesson_id);
        $this->assertEquals($exercise->id, $this->responseView()->userExercise->exercise_id);
    }

    /** @test */
    public function itShould_showLessonLearnPage_withPreviousExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $lesson->subscribe($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $previous = $lesson->exercises[0];
        $exercise = $this->createExercise();
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')->with($lesson->id)
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises, $previous->id)
            ->willReturn($userExercise);

        $this->call('GET', '/learn/lessons/' . $lesson->id . '?previous_exercise_id=' . $previous->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->responseView()->userLesson->lesson_id);
        $this->assertEquals($exercise->id, $this->responseView()->userExercise->exercise_id);
    }

    /** @test */
    public function itShould_showLessonLearnPage_withRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $lesson->exercises[0];
        $userExercise = $this->createUserExercise($user, $requested);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExerciseOfExercise')->with($requested->id)
            ->willReturn($userExercise);

        $this->call('GET', '/learn/lessons/' . $lesson->id . '?requested_exercise_id=' . $requested->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->responseView()->userLesson->lesson_id);
        $this->assertEquals($requested->id, $this->responseView()->userExercise->exercise_id);
    }

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson_lessonIsBidirectional()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $lesson->subscribe($user);
        $this->updateBidirectional($lesson, $user->id, $bidirectional = true);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $this->createExercise();
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')->with($lesson->id)
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises)
            ->willReturn($userExercise);

        /** @var UserExerciseModifier|MockObject $userExerciseModifier */
        $userExerciseModifier = $this->createMock(UserExerciseModifier::class);
        $this->instance(UserExerciseModifier::class, $userExerciseModifier);
        $userExerciseModifier->expects($this->once())->method('swapQuestionWithAnswer')
            ->with($userExercise)->willReturn($userExercise);

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->responseView()->userLesson->lesson_id);
        $this->assertEquals($exercise->id, $this->responseView()->userExercise->exercise_id);
    }

    /** @test */
    public function itShould_showLessonLearnPage_noMoreExercisesForToday()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $lesson->subscribe($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $this->createExercise();
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')->with($lesson->id)
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises)
            ->willReturn(null);

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->responseView()->userLesson->lesson_id);
        $this->assertEquals(null, $this->responseView()->userExercise);
        $this->assertEquals(null, $this->responseView()->canEditExercise);
        $this->assertEquals(null, $this->responseView()->editExerciseUrl);
    }

    // handleGoodAnswer

    /** @test */
    public function itShould_handleGoodAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')->with($lesson->id)
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises)
            ->willReturn($userExercise);

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'good'
        ];

        $this->expectsEvents(ExerciseGoodAnswer::class);

        $this->call('POST', '/learn/lessons/' . $lesson->id, $data);

        $this->assertResponseOk();
    }

    // handleBadAnswer

    /** @test */
    public function itShould_handleBadAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfLesson')->with($lesson->id)
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises)
            ->willReturn($userExercise);

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'bad'
        ];

        $this->expectsEvents(ExerciseBadAnswer::class);

        $this->call('POST', '/learn/lessons/' . $lesson->id, $data);

        $this->assertResponseOk();
    }
}
