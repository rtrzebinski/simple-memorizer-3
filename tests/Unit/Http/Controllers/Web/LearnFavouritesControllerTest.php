<?php

namespace Tests\Unit\Http\Controllers\Web;

use App\Services\LearningService;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use WebTestCase;

class LearnFavouritesControllerTest extends WebTestCase
{
    // learn

    /** @test */
    public function itShould_showLearnFavouritesPage()
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
        $userExerciseRepository->method('fetchUserExercisesOfFavouriteLessons')
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises)
            ->willReturn($userExercise);

        $this->call('GET', '/learn/favourites/');

        $this->assertResponseOk();
        $this->assertEquals($userExercise, $this->responseView()->userExercise);
    }

    /** @test */
    public function itShould_showLearnFavouritesPage_withPreviousExercise()
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
        $userExerciseRepository->method('fetchUserExercisesOfFavouriteLessons')
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises, $previous->id)
            ->willReturn($userExercise);

        $this->call('GET', '/learn/favourites/?previous_exercise_id=' . $previous->id);

        $this->assertResponseOk();
        $this->assertEquals($exercise->id, $this->responseView()->userExercise->exercise_id);
    }

    /** @test */
    public function itShould_showLearnFavouritesPage_withRequestedExercise()
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

        $this->call('GET', '/learn/favourites/?requested_exercise_id=' . $requested->id);

        $this->assertResponseOk();
        $this->assertEquals($requested->id, $this->responseView()->userExercise->exercise_id);
    }

    /** @test */
    public function itShould_showLearnFavouritesPage_noMoreExercisesForToday()
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
        $userExerciseRepository->method('fetchUserExercisesOfFavouriteLessons')
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises)
            ->willReturn(null);

        $this->call('GET', '/learn/favourites/');

        $this->assertResponseOk();
        $this->assertEquals(null, $this->responseView()->userExercise);
        $this->assertEquals(null, $this->responseView()->canEditExercise);
        $this->assertEquals(null, $this->responseView()->editExerciseUrl);
    }

    /** @test */
    public function itShould_notShowLearnFavouritesPage_unauthorized()
    {
        $lesson = $this->createLesson();

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

        $this->call('GET', '/learn/favourites/?requested_exercise_id=' . $requested->id);

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
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfFavouriteLessons')
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises, $exercise->id)
            ->willReturn($userExercise);

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'good'
        ];

        $this->call('POST', '/learn/favourites/', $data);

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

        $this->call('POST', '/learn/favourites/');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/favourites/');

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
        $userExercise = $this->createUserExercise($user, $exercise);
        $userExercises = collect([$userExercise]);

        /** @var AuthenticatedUserExerciseRepositoryInterface|MockObject $userExerciseRepository */
        $userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->instance(AuthenticatedUserExerciseRepositoryInterface::class, $userExerciseRepository);
        $userExerciseRepository->method('fetchUserExercisesOfFavouriteLessons')
            ->willReturn($userExercises);

        /** @var LearningService|MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('findUserExerciseToLearn')
            ->with($userExercises, $exercise->id)
            ->willReturn($userExercise);

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'bad'
        ];

        $this->call('POST', '/learn/favourites/', $data);

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

        $this->call('POST', '/learn/favourites/');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notHandleBadAnswer_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/favourites/');

        $this->assertResponseInvalidInput();
    }
}
