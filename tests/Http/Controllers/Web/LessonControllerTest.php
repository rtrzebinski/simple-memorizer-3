<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Lesson;
use Illuminate\Http\UploadedFile;
use League\Csv\Writer;

class LessonControllerTest extends BaseTestCase
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
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
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

        $this->assertResponseNotFound();
    }

    // exercises

    /** @test */
    public function itShould_showLessonExercisesViewPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
    }

    /** @test */
    public function itShould_showLessonExercisesViewPage_canModifyLesson()
    {
        $this->be($user = $this->createUser());
        // user is lesson owner, so cam modify it
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
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
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
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
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
        $this->assertEquals($lesson->id, $this->view()->exercises->first()->id);
        $this->assertEquals(0, $this->view()->exercises[0]->percent_of_good_answers);
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
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
        $this->assertEquals($lesson->id, $this->view()->exercises->first()->id);
        $this->assertEquals(66, $this->view()->exercises[0]->percent_of_good_answers);
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
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
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

        $this->assertResponseNotFound();
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
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
    }

    /** @test */
    public function itShould_notShowLessonSettingsPage_userDoesNotSubscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($this->createUser());

        $this->call('GET', '/lessons/'.$lesson->id.'/settings');

        $this->assertResponseStatus(500);
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

        /** @var Lesson $lesson */
        $lesson = $lesson->fresh();
        $this->assertEquals($input['bidirectional'], $lesson->isBidirectional($user->id));
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
        $this->assertResponseRedirectedBack();
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
        $this->assertResponseRedirectedBack();
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
    public function itShould_notSubscribeAndLearn_unauthorized()
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

    // exportCsv

    /** @test */
    public function itShould_exportCsv()
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

        $this->call('GET', '/lessons/'.$lesson->id.'/csv');

        $this->assertEquals('application/force-download', $this->response->headers->get('content-type'));
        $this->assertEquals('attachment; filename="'.$lesson->name.'.csv"',
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

    /** @test */
    public function itShould_notExportCsv_unauthorised()
    {
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/csv');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notExportCsv_lessonNotFound()
    {
        $user = $this->createUser();
        $this->be($user);

        $this->call('GET', '/lessons/-1/csv');

        $this->assertResponseNotFound();
    }

    // importCsv

    /** @test */
    public function itShould_importCsv()
    {
        $user = $this->createUser();
        $this->be($user);
        $lesson = $this->createPrivateLesson($user);

        $path = tempnam(sys_get_temp_dir(), uniqid());

        $file = $this->createUploadedFileMock('csv');
        $file->method('getRealPath')->willReturn($path);

        /** @var Writer $writer */
        $writer = Writer::createFromPath($path);

        $writer->insertOne([
            'question',
            'answer',
            'number_of_good_answers',
            'number_of_bad_answers',
            'percent_of_good_answers',
        ]);

        $writer->insertOne($data = [
            $question = uniqid(),
            $answer = uniqid(),
            $numberOfGoodAnswers = 2,
            $numberOfBadAnswers = 8,
            $percentOfGoodAnswers = 80,
        ]);

        $this->call('POST', '/lessons/'.$lesson->id.'/csv', $parameters = [], $cookies = [], ['csv_file' => $file]);

        $this->assertResponseRedirectedTo('/lessons/'.$lesson->id);
        $this->assertCount(1, $lesson->exercises);
        $this->assertEquals($data[0], $lesson->exercises->first()->question);
        $this->assertEquals($data[1], $lesson->exercises->first()->answer);
        $this->assertEquals($lesson->id, $lesson->exercises->first()->lesson_id);
        $this->assertEquals(2, $lesson->exercises->first()->resultOfUser($user->id)->number_of_good_answers);
        $this->assertEquals(8, $lesson->exercises->first()->resultOfUser($user->id)->number_of_bad_answers);
        $this->assertEquals(80, $lesson->exercises->first()->resultOfUser($user->id)->percent_of_good_answers);
    }

    /** @test */
    public function itShould_notImportCsv_unauthorized()
    {
        $lesson = $this->createLesson();

        /** @var string $path */
        $path = tempnam(sys_get_temp_dir(), uniqid());

        /** @var UploadedFile $file */
        $file = new UploadedFile($path, uniqid());

        /** @var Writer $writer */
        $writer = Writer::createFromPath($path);

        $writer->insertOne([
            'question',
            'answer',
            'number_of_good_answers',
            'number_of_bad_answers',
            'percent_of_good_answers',
        ]);

        $writer->insertOne($data = [
            $question = uniqid(),
            $answer = uniqid(),
            $numberOfGoodAnswers = 2,
            $numberOfBadAnswers = 8,
            $percentOfGoodAnswers = 80,
        ]);

        $this->call('POST', '/lessons/'.$lesson->id.'/csv', $parameters = [], $cookies = [], ['csv_file' => $file]);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notImportCsv_forbidden()
    {
        $user = $this->createUser();
        $this->be($user);
        $lesson = $this->createPrivateLesson();

        /** @var string $path */
        $path = tempnam(sys_get_temp_dir(), uniqid());

        /** @var UploadedFile $file */
        $file = new UploadedFile($path, uniqid());

        /** @var Writer $writer */
        $writer = Writer::createFromPath($path);

        $writer->insertOne([
            'question',
            'answer',
            'number_of_good_answers',
            'number_of_bad_answers',
            'percent_of_good_answers',
        ]);

        $writer->insertOne($data = [
            $question = uniqid(),
            $answer = uniqid(),
            $numberOfGoodAnswers = 2,
            $numberOfBadAnswers = 8,
            $percentOfGoodAnswers = 80,
        ]);

        $this->call('POST', '/lessons/'.$lesson->id.'/csv', $parameters = [], $cookies = [], ['csv_file' => $file]);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notImportCsv_lessonNotFound()
    {
        $user = $this->createUser();
        $this->be($user);

        /** @var string $path */
        $path = tempnam(sys_get_temp_dir(), uniqid());

        /** @var UploadedFile $file */
        $file = new UploadedFile($path, uniqid());

        /** @var Writer $writer */
        $writer = Writer::createFromPath($path);

        $writer->insertOne([
            'question',
            'answer',
            'number_of_good_answers',
            'number_of_bad_answers',
            'percent_of_good_answers',
        ]);

        $writer->insertOne($data = [
            $question = uniqid(),
            $answer = uniqid(),
            $numberOfGoodAnswers = 2,
            $numberOfBadAnswers = 8,
            $percentOfGoodAnswers = 80,
        ]);

        $this->call('POST', '/lessons/-1/csv', $parameters = [], $cookies = [], ['csv_file' => $file]);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notImportCsv_invalidInput()
    {
        $user = $this->createUser();
        $this->be($user);
        $lesson = $this->createPrivateLesson($user);

        $this->call('POST', '/lessons/'.$lesson->id.'/csv');

        $this->assertResponseInvalidInput();
    }
}
