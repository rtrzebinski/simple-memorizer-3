<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Exercise\ExerciseRepository;

class LearnControllerTest extends BaseTestCase
{
    // learn

    public function testItShould_showLessonLearnPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $this->createExercise();

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->method('fetchRandomExerciseOfLesson')
            ->with($lesson->id, $user->id)
            ->willReturn($exercise);

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
        $this->assertEquals($exercise->id, $this->view()->exercise->id);
    }

    public function testItShould_showLessonLearnPage_withPreviousExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $previous = $lesson->exercises[0];
        $exercise = $this->createExercise();

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->method('fetchRandomExerciseOfLesson')
            ->with($lesson->id, $user->id, $previous->id)
            ->willReturn($exercise);

        $this->call('GET', '/learn/lessons/' . $lesson->id . '?previous_exercise_id=' . $previous->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
        $this->assertEquals($exercise->id, $this->view()->exercise->id);
    }

    public function testItShould_showLessonLearnPage_withRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $lesson->exercises[0];

        $this->call('GET', '/learn/lessons/' . $lesson->id . '?requested_exercise_id=' . $requested->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
        $this->assertEquals($requested->id, $this->view()->exercise->id);
    }

    public function testItShould_notShowLessonLearnPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertUnauthorized();
    }

    public function testItShould_notShowLessonLearnPage_forbiddenToLearnLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/learn/lessons/' . $lesson->id);

        $this->assertForbidden();
    }

    public function testItShould_notShowLessonLearnPage_forbiddenToAccessRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $this->createExercise();

        $this->call('GET', '/learn/lessons/' . $lesson->id . '?requested_exercise_id=' . $requested->id);

        $this->assertForbidden();
    }

    public function testItShould_notShowLessonLearnPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/learn/lessons/-1');

        $this->assertNotFound();
    }

    // handleGoodAnswer

    public function testItShould_handleGoodAnswer()
    {
        $this->be($user = $this->createUser());

        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises[0];

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->expects($this->once())
            ->method('handleGoodAnswer')
            ->with($exercise->id, $user->id);

        $this->call('POST', '/learn/handle-good-answer/exercises/' . $exercise->id);

        $this->assertRedirectedTo('/learn/lessons/' . $exercise->lesson_id . '?previous_exercise_id=' . $exercise->id);
    }

    public function testItShould_notHandleGoodAnswer_unauthorized()
    {
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises[0];

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->expects($this->never())
            ->method('handleGoodAnswer');

        $this->call('POST', '/learn/handle-good-answer/exercises/' . $exercise->id);

        $this->assertUnauthorized();
    }

    public function testItShould_notHandleGoodAnswer_forbidden()
    {
        $this->be($user = $this->createUser());
        $exercise = $this->createExercise();

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->expects($this->never())
            ->method('handleGoodAnswer');

        $this->call('POST', '/learn/handle-good-answer/exercises/' . $exercise->id);

        $this->assertForbidden();
    }

    public function testItShould_notHandleGoodAnswer_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->expects($this->never())
            ->method('handleGoodAnswer');

        $this->call('POST', '/learn/handle-good-answer/exercises/-1');

        $this->assertNotFound();
    }

    // handleBadAnswer

    public function testItShould_handleBadAnswer()
    {
        $this->be($user = $this->createUser());

        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises[0];

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->expects($this->once())
            ->method('handleBadAnswer')
            ->with($exercise->id, $user->id);

        $this->call('POST', '/learn/handle-bad-answer/exercises/' . $exercise->id);

        $this->assertRedirectedTo('/learn/lessons/' . $exercise->lesson_id . '?previous_exercise_id=' . $exercise->id);
    }

    public function testItShould_notHandleBadAnswer_unauthorized()
    {
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises[0];

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->expects($this->never())
            ->method('handleBadAnswer');

        $this->call('POST', '/learn/handle-bad-answer/exercises/' . $exercise->id);

        $this->assertUnauthorized();
    }

    public function testItShould_notHandleBadAnswer_forbidden()
    {
        $this->be($user = $this->createUser());
        $exercise = $this->createExercise();

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->expects($this->never())
            ->method('handleBadAnswer');

        $this->call('POST', '/learn/handle-bad-answer/exercises/' . $exercise->id);

        $this->assertForbidden();
    }

    public function testItShould_notHandleBadAnswer_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $exerciseRepository);

        $exerciseRepository->expects($this->never())
            ->method('handleBadAnswer');

        $this->call('POST', '/learn/handle-bad-answer/exercises/-1');

        $this->assertNotFound();
    }

    // updateExercise

    public function testItShould_updateExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/learn/exercises/' . $exercise->id, $parameters);

        $exercise = $exercise->fresh();
        $this->assertEquals($parameters['question'], $exercise->question);
        $this->assertEquals($parameters['answer'], $exercise->answer);
        $this->assertRedirectedTo('/learn/lessons/' . $exercise->lesson_id . '?requested_exercise_id=' . $exercise->id);
    }

    public function testItShould_notUpdateExercise_unauthorized()
    {
        $lesson = $this->createPublicLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/learn/exercises/' . $exercise->id, $parameters);

        $this->assertUnauthorized();
    }

    public function testItShould_notUpdateExercise_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/learn/exercises/' . $exercise->id, $parameters);

        $this->assertForbidden();
    }

    public function testItShould_notUpdateExercise_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/learn/exercises/-1', $parameters);

        $this->assertNotFound();
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('PUT', '/learn/exercises/' . $exercise->id);

        $this->assertInvalidInput();
    }
}
