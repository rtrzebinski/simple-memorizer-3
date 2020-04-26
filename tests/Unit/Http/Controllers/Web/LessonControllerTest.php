<?php

namespace Tests\Unit\Http\Controllers\Web;

use App\Models\Lesson;

class LessonControllerTest extends TestCase
{
    // create

    /** @test */
    public function itShould_showLessonCreatePage()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/create');

        $this->assertResponseOk();
    }

    /** @test */
    public function itShould_notShowLessonCreatePage_unauthorized()
    {
        $this->call('GET', '/lessons/create');

        $this->assertResponseUnauthorized();
    }

    // store

    /** @test */
    public function itShould_storeLesson()
    {
        $this->be($user = $this->createUser());

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->call('POST', '/lessons', $input);

        /** @var Lesson $lesson */
        $lesson = $this->last(Lesson::class);
        $this->assertEquals($input['name'], $lesson->name);
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertResponseRedirectedTo('/lessons/'.$lesson->id);

        // ensure user and a lesson have a row in pivot table,
        // but it should not be considered a regular subscriber
        $this->assertDatabaseHas('lesson_user', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'percent_of_good_answers' => 0,
        ]);
        $this->assertCount(1, $lesson->subscribedUsers);
    }

    /** @test */
    public function itShould_notStoreLesson_unauthorized()
    {
        $this->call('POST', '/lessons');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notStoreLesson_invalidInput()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons');

        $this->assertResponseInvalidInput();
    }

    // view

    /** @test */
    public function itShould_showLessonViewPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/'.$lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
    }

    /** @test */
    public function itShould_showLessonViewPage_guestUser()
    {
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/'.$lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
    }

    /** @test */
    public function itShould_notShowLessonViewPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/'.$lesson->id);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notShowLessonViewPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1');

        $this->assertResponseForbidden();
    }

    // exercises

    /** @test */
    public function itShould_showLessonExercisesViewPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
    }

    /** @test */
    public function itShould_showLessonExercisesViewPage_guestUser()
    {
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
    }

    /** @test */
    public function itShould_showLessonExercisesViewPage_canModifyLesson()
    {
        $this->be($user = $this->createUser());
        // user is lesson owner, so cam modify it
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
        $this->assertEquals(true, $this->view()->canModifyLesson);
    }

    /** @test */
    public function itShould_showLessonExercisesViewPage_canNotModifyLesson()
    {
        $this->be($user = $this->createUser());
        // user is not lesson owner, so cam modify it
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
        $this->assertEquals(false, $this->view()->canModifyLesson);
    }

    /** @test */
    public function itShould_showLessonExercisesViewPage_exerciseWithoutAnswers()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();
        $this->createExercise([
            'lesson_id' => $lesson->id,
        ]);

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
        $this->assertEquals($lesson->id, $this->view()->userExercises->first()->exercise_id);
        $this->assertEquals(0, $this->view()->userExercises[0]->percent_of_good_answers);
    }

    /** @test */
    public function itShould_showLessonExercisesViewPage_exerciseWithGoodAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();
        $exercise = $this->createExercise([
            'lesson_id' => $lesson->id,
        ]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 66,
        ]);

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
        $this->assertEquals($lesson->id, $this->view()->userExercises->first()->exercise_id);
        $this->assertEquals(66, $this->view()->userExercises[0]->percent_of_good_answers);
    }

    /** @test */
    public function itShould_notShowLessonExercisesViewPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notShowLessonExercisesViewPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/exercises');

        $this->assertResponseNotFound();
    }

    // edit

    /** @test */
    public function itShould_showLessonEditPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
    }

    /** @test */
    public function itShould_notShowLessonEditPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notShowLessonEditPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notShowLessonEditPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/edit');

        $this->assertResponseForbidden();
    }

    // saveEdit

    /** @test */
    public function itShould_updateLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $input = [
            'visibility' => 'private',
            'name' => uniqid(),
        ];

        $this->call('PUT', '/lessons/'.$lesson->id.'/edit', $input);

        $this->assertResponseStatus(302);
        $this->assertResponseRedirectedTo('/lessons/'.$lesson->id.'/edit');

        /** @var Lesson $lesson */
        $lesson = $lesson->fresh();
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertEquals($input['name'], $lesson->name);
    }

    /** @test */
    public function itShould_notUpdateLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('PUT', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notUpdateLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('PUT', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notUpdateLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('PUT', '/lessons/-1/edit');

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notUpdateLesson_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('PUT', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseInvalidInput();
    }

    // delete

    /** @test */
    public function itShould_deleteLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('DELETE', '/lessons/'.$lesson->id);

        $this->assertResponseRedirectedTo('/home');
        $this->assertNull($lesson->fresh());
    }

    /** @test */
    public function itShould_notDeleteLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('DELETE', '/lessons/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notDeleteLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('DELETE', '/lessons/'.$lesson->id);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notDeleteLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('DELETE', '/lessons/-1');

        $this->assertResponseNotFound();
    }

    // settings

    /** @test */
    public function itShould_showLessonSettingsPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/'.$lesson->id.'/settings');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->userLesson->lesson_id);
    }

    /** @test */
    public function itShould_notShowLessonSettingsPage_userDoesNotSubscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($this->createUser());

        $this->call('GET', '/lessons/'.$lesson->id.'/settings');

        $this->assertResponseForbidden();
    }

    // saveSettings

    /** @test */
    public function itShould_saveLessonSettings()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $input = [
            'bidirectional' => '1',
        ];

        $this->call('PUT', '/lessons/'.$lesson->id.'/settings', $input);

        $this->assertResponseStatus(302);
        $this->assertResponseRedirectedTo('/lessons/'.$lesson->id.'/settings');

        $this->assertEquals($input['bidirectional'], $this->isBidirectional($lesson, $user->id));
    }

    /** @test */
    public function itShould_saveLessonSettings_unauthorised()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($this->createUser());

        $input = [
            'bidirectional' => '1',
        ];

        $this->call('PUT', '/lessons/'.$lesson->id.'/settings', $input);

        $this->assertResponseStatus(500);
    }

    /** @test */
    public function itShould_saveLessonSettings_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('PUT', '/lessons/'.$lesson->id.'/settings');

        $this->assertResponseInvalidInput();
    }

    // subscribe

    /** @test */
    public function itShould_subscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe');

        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);
        $this->assertResponseRedirectedTo('/home');
    }

    /** @test */
    public function itShould_notSubscribeLesson_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notSubscribeLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertResponseRedirectedTo('/home');
    }

    /** @test */
    public function itShould_notSubscribeLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/subscribe');

        $this->assertResponseNotFound();
    }

    // unsubscribe

    /** @test */
    public function itShould_unsubscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->call('POST', '/lessons/'.$lesson->id.'/unsubscribe');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertResponseRedirectedBack();
    }

    /** @test */
    public function itShould_notUnsubscribeLesson_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/unsubscribe');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notUnsubscribeLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/unsubscribe');

        $this->assertResponseRedirectedBack();
    }

    /** @test */
    public function itShould_notUnsubscribeLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/unsubscribe');

        $this->assertResponseNotFound();
    }

    // subscribeAndLearn

    /** @test */
    public function itShould_subscribeAndLearn()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe-and-learn');

        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);
        $this->assertResponseRedirectedTo('/learn/lessons/'.$lesson->id);
    }

    /** @test */
    public function itShould_notSubscribeAndLearn_guestUser()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe-and-learn');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notSubscribeAndLearn_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe-and-learn');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertResponseRedirectedTo('/learn/lessons/'.$lesson->id);
    }

    /** @test */
    public function itShould_notSubscribeAndLearn_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/subscribe-and-learn');

        $this->assertResponseNotFound();
    }
}
