<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Lesson;
use App\Services\LearningService;

class LearningControllerTest extends BaseTestCase
{
    // learn

    public function testItShould_showLessonLearnPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $this->createExercise();

        /** @var LearningService|\PHPUnit_Framework_MockObject_MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('fetchRandomExerciseOfLesson')
            ->with($this->isInstanceOf(Lesson::class), $user->id)
            ->willReturn($exercise);

        $this->call('GET', '/learn/lessons/'.$lesson->id);

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

        /** @var LearningService|\PHPUnit_Framework_MockObject_MockObject $learningService */
        $learningService = $this->createMock(LearningService::class);
        $this->instance(LearningService::class, $learningService);

        $learningService->method('fetchRandomExerciseOfLesson')
            ->with($this->isInstanceOf(Lesson::class), $user->id, $previous->id)
            ->willReturn($exercise);

        $this->call('GET', '/learn/lessons/'.$lesson->id.'?previous_exercise_id='.$previous->id);

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

        $this->call('GET', '/learn/lessons/'.$lesson->id.'?requested_exercise_id='.$requested->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
        $this->assertEquals($requested->id, $this->view()->exercise->id);
    }

    public function testItShould_notShowLessonLearnPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/learn/lessons/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notShowLessonLearnPage_forbiddenToLearnLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/learn/lessons/'.$lesson->id);

        $this->assertResponseForbidden();
    }

    public function testItShould_notShowLessonLearnPage_forbiddenToAccessRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $this->createExercise();

        $this->call('GET', '/learn/lessons/'.$lesson->id.'?requested_exercise_id='.$requested->id);

        $this->assertResponseForbidden();
    }

    public function testItShould_notShowLessonLearnPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/learn/lessons/-1');

        $this->assertResponseNotFound();
    }

    // handleGoodAnswer

    public function testItShould_handleGoodAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $this->call('POST', '/learn/handle-good-answer/exercises/'.$exercise->id.'/'.$lesson->id);

        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(100, $exercise->percentOfGoodAnswersOfUser($user->id));
        $this->assertEquals(50, $lesson->percentOfGoodAnswersOfUser($user->id));

        $this->assertResponseRedirectedTo('/learn/lessons/'.$exercise->lesson_id.'?previous_exercise_id='.$exercise->id);
    }

    public function testItShould_notHandleGoodAnswer_unauthorized()
    {
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises[0];

        $this->call('POST', '/learn/handle-good-answer/exercises/'.$exercise->id.'/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notHandleGoodAnswer_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $exercise = $lesson->exercises[0];

        $this->call('POST', '/learn/handle-good-answer/exercises/'.$exercise->id.'/'.$lesson->id);

        $this->assertResponseForbidden();
    }

    public function testItShould_notHandleGoodAnswer_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/learn/handle-good-answer/exercises/-1/1');

        $this->assertResponseNotFound();
    }

    // handleBadAnswer

    public function testItShould_handleBadAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises->first();

        $this->call('POST', '/learn/handle-bad-answer/exercises/'.$exercise->id.'/'.$lesson->id);

        $this->assertEquals(0, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
        $this->assertEquals(0, $lesson->percentOfGoodAnswersOfUser($user->id));

        $this->assertResponseRedirectedTo('/learn/lessons/'.$exercise->lesson_id.'?previous_exercise_id='.$exercise->id);
    }

    public function testItShould_notHandleBadAnswer_unauthorized()
    {
        $lesson = $this->createExercise()->lesson;
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises[0];

        $this->call('POST', '/learn/handle-bad-answer/exercises/'.$exercise->id.'/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notHandleBadAnswer_forbidden()
    {
        $this->be($user = $this->createUser());
        $exercise = $this->createExercise();

        $this->call('POST', '/learn/handle-bad-answer/exercises/'.$exercise->id.'/'.$exercise->lesson_id);

        $this->assertResponseForbidden();
    }

    public function testItShould_notHandleBadAnswer_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/learn/handle-bad-answer/exercises/-1');

        $this->assertResponseNotFound();
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

        $this->call('PUT', '/learn/exercises/'.$exercise->id.'/'.$lesson->id, $parameters);

        $exercise = $exercise->fresh();
        $this->assertEquals($parameters['question'], $exercise->question);
        $this->assertEquals($parameters['answer'], $exercise->answer);
        $this->assertResponseRedirectedTo('/learn/lessons/'.$lesson->id.'?requested_exercise_id='.$exercise->id);
    }

    public function testItShould_notUpdateExercise_unauthorized()
    {
        $lesson = $this->createPublicLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/learn/exercises/'.$exercise->id.'/'.$lesson->id, $parameters);

        $this->assertResponseUnauthorized();
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

        $this->call('PUT', '/learn/exercises/'.$exercise->id.'/'.$lesson->id, $parameters);

        $this->assertResponseForbidden();
    }

    public function testItShould_notUpdateExercise_exerciseNotFound()
    {
        $this->be($user = $this->createUser());

        $parameters = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->call('PUT', '/learn/exercises/-1/1', $parameters);

        $this->assertResponseNotFound();
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->call('PUT', '/learn/exercises/'.$exercise->id.'/'.$lesson->id);

        $this->assertResponseInvalidInput();
    }
}
