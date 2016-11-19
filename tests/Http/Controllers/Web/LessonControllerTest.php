<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Lesson\Lesson;

class LessonControllerTest extends BaseTestCase
{
    // create

    public function testItShould_showLessonCreatePage()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/create');

        $this->assertResponseOk();
    }

    public function testItShould_notShowLessonCreatePage_unauthorized()
    {
        $this->call('GET', '/lessons/create');

        $this->assertUnauthorized();
    }

    // store

    public function testItShould_storeLesson()
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
        $this->assertRedirectedTo('/lessons/' . $lesson->id);
    }

    public function testItShould_notStoreLesson_unauthorized()
    {
        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->call('POST', '/lessons', $input);

        $this->assertUnauthorized();
    }

    public function testItShould_notStoreLesson_invalidInput()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons');

        $this->assertInvalidInput();
    }

    // view

    public function testItShould_showLessonViewPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/' . $lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
    }

    public function testItShould_notShowLessonViewPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id);

        $this->assertUnauthorized();
    }

    public function testItShould_notShowLessonViewPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/' . $lesson->id);

        $this->assertForbidden();
    }

    public function testItShould_notShowLessonViewPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1');

        $this->assertNotFound();
    }

    // edit

    public function testItShould_showLessonEditPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/' . $lesson->id . '/edit');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
    }

    public function testItShould_notShowLessonEditPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/edit');

        $this->assertUnauthorized();
    }

    public function testItShould_notShowLessonEditPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/edit');

        $this->assertForbidden();
    }

    public function testItShould_notShowLessonEditPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/edit');

        $this->assertNotFound();
    }

    // update

    public function testItShould_updateLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $input = [
            'visibility' => 'private',
            'name' => uniqid(),
        ];

        $this->call('PUT', '/lessons/' . $lesson->id, $input);

        /** @var Lesson $lesson */
        $lesson = $lesson->fresh();
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertEquals($input['name'], $lesson->name);
        $this->assertRedirectedTo('/lessons/' . $lesson->id);
    }

    public function testItShould_notUpdateLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $input = [
            'visibility' => 'private',
            'name' => uniqid(),
        ];

        $this->call('PUT', '/lessons/' . $lesson->id, $input);

        $this->assertUnauthorized();
    }

    public function testItShould_notUpdateLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $input = [
            'visibility' => 'private',
            'name' => uniqid(),
        ];

        $this->call('PUT', '/lessons/' . $lesson->id, $input);

        $this->assertForbidden();
    }

    public function testItShould_notUpdateLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $input = [
            'visibility' => 'private',
            'name' => uniqid(),
        ];

        $this->call('PUT', '/lessons/-1', $input);

        $this->assertNotFound();
    }

    public function testItShould_notUpdateLesson_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('PUT', '/lessons/' . $lesson->id);

        $this->assertInvalidInput();
    }

    // delete

    public function testItShould_deleteLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('DELETE', '/lessons/' . $lesson->id);

        $this->assertRedirectedTo('/home');
        $this->assertNull($lesson->fresh());
    }

    public function testItShould_notDeleteLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('DELETE', '/lessons/' . $lesson->id);

        $this->assertUnauthorized();
    }

    public function testItShould_notDeleteLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('DELETE', '/lessons/' . $lesson->id);

        $this->assertForbidden();
    }

    public function testItShould_notDeleteLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('DELETE', '/lessons/-1');

        $this->assertNotFound();
    }

    // subscribe

    public function testItShould_subscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);
        $this->assertRedirectedBack();
    }

    public function testItShould_notSubscribeLesson_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        $this->assertUnauthorized();
    }

    public function testItShould_notSubscribeLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertRedirectedBack();
    }

    public function testItShould_notSubscribeLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/subscribe');

        $this->assertNotFound();
    }

    // unsubscribe

    public function testItShould_unsubscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->call('POST', '/lessons/' . $lesson->id . '/unsubscribe');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertRedirectedBack();
    }

    public function testItShould_notUnsubscribeLesson_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/unsubscribe');

        $this->assertUnauthorized();
    }

    public function testItShould_notUnsubscribeLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/unsubscribe');

        $this->assertRedirectedBack();
    }

    public function testItShould_notUnsubscribeLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/unsubscribe');

        $this->assertNotFound();
    }

    // subscribeAndLearn

    public function testItShould_subscribeAndLearn()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe-and-learn');

        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);
        $this->assertRedirectedTo('/learn/lessons/' . $lesson->id);
    }

    public function testItShould_notSubscribeAndLearn_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe-and-learn');

        $this->assertUnauthorized();
    }

    public function testItShould_notSubscribeAndLearn_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe-and-learn');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertRedirectedTo('/learn/lessons/' . $lesson->id);
    }

    public function testItShould_notSubscribeAndLearn_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/subscribe-and-learn');

        $this->assertNotFound();
    }

    // exportCsv

    public function testItShould_exportCsv()
    {
        $user = $this->createUser();
        $this->be($user);
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $result = $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'number_of_good_answers' => 3,
            'number_of_bad_answers' => 1,
            'percent_of_good_answers' => 75,
        ]);

        $this->call('GET', '/lessons/' . $lesson->id . '/csv');

        $this->assertEquals('application/force-download', $this->response->headers->get('content-type'));
        $this->assertEquals('attachment; filename="' . $lesson->name . '.csv"',
            $this->response->headers->get('content-Disposition'));

        $content = $this->response->content();
        $lines = explode(PHP_EOL, $content);

        $header = str_getcsv($lines[0]);
        $this->assertEquals([
            'question',
            'answer',
            'number_of_good_answers',
            'number_of_bad_answers',
            'percent_of_good_answers',
        ], $header);

        $first = str_getcsv($lines[1]);
        $this->assertEquals([
            $exercise->question,
            $exercise->answer,
            $result->number_of_good_answers,
            $result->number_of_bad_answers,
            $result->percent_of_good_answers,
        ], $first);
    }
}
