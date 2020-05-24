<?php

namespace Tests\Unit\Http\Controllers\Web;

use App\Services\LearningService;
use App\Services\UserExerciseModifier;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use WebTestCase;

class LearnLessonControllerTest extends WebTestCase
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

        $this->call('GET', '/learn/lessons/'.$lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
        $this->assertEquals($exercise->id, $this->view()->userExercise->exercise_id);
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

        $this->call('GET', '/learn/lessons/'.$lesson->id.'?previous_exercise_id='.$previous->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
        $this->assertEquals($exercise->id, $this->view()->userExercise->exercise_id);
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

        $this->call('GET', '/learn/lessons/'.$lesson->id.'?requested_exercise_id='.$requested->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
        $this->assertEquals($requested->id, $this->view()->userExercise->exercise_id);
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

        $this->call('GET', '/learn/lessons/'.$lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
        $this->assertEquals($exercise->id, $this->view()->userExercise->exercise_id);
    }

    /** @test */
    public function itShould_notShowLessonLearnPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/learn/lessons/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notShowLessonLearnPage_forbiddenToAccessRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $this->createExercise();

        $this->call('GET', '/learn/lessons/'.$lesson->id.'?requested_exercise_id='.$requested->id);

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

        $this->call('GET', '/learn/lessons/'.$lesson->id);

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

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'good'
        ];

        $this->call('POST', '/learn/lessons/'.$lesson->id, $data);

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

        $this->call('POST', '/learn/lessons/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notHandleGoodAnswer_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/lessons/'.$lesson->id);

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

        $data = [
            'previous_exercise_id' => $exercise->id,
            'answer' => 'bad'
        ];

        $this->call('POST', '/learn/lessons/'.$lesson->id, $data);

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

        $this->call('POST', '/learn/lessons/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notHandleBadAnswer_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('POST', '/learn/lessons/'.$lesson->id);

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

        $this->call('PUT', '/learn/lessons/'.$exercise->id.'/'.$lesson->id, $parameters);

        $this->assertResponseRedirectedTo('/learn/lessons/'.$lesson->id.'?requested_exercise_id='.$exercise->id);
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

        $this->call('PUT', '/learn/lessons/'.$exercise->id.'/'.$lesson->id, $parameters);

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

        $this->call('PUT', '/learn/lessons/'.$exercise->id.'/'.$lesson->id, $parameters);

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

        $this->call('PUT', '/learn/lessons/-1/1', $parameters);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notUpdateExercise_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('PUT', '/learn/lessons/'.$exercise->id.'/'.$lesson->id);

        $this->assertResponseInvalidInput();
    }
}