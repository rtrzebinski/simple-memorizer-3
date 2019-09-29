<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Lesson;
use Illuminate\Http\UploadedFile;
use League\Csv\Writer;

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

        $this->assertResponseUnauthorized();
    }

    // store

    public function testItShould_storeLesson()
    {
        $this->be($user = $this->createUser());

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
            'bidirectional' => '1',
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
        $this->assertCount(1, $lesson->subscribers);
        $this->assertCount(0, $lesson->subscribersWithOwnerExcluded);
    }

    public function testItShould_notStoreLesson_unauthorized()
    {
        $this->call('POST', '/lessons');

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notStoreLesson_invalidInput()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons');

        $this->assertResponseInvalidInput();
    }

    // view

    public function testItShould_showLessonViewPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/'.$lesson->id);

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
    }

    public function testItShould_notShowLessonViewPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notShowLessonViewPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/'.$lesson->id);

        $this->assertResponseForbidden();
    }

    public function testItShould_notShowLessonViewPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1');

        $this->assertResponseNotFound();
    }

    // exercises

    public function testItShould_showLessonExercisesViewPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
    }

    public function testItShould_showLessonExercisesViewPage_exerciseWithoutAnswers()
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

    public function testItShould_showLessonExercisesViewPage_exerciseWithGoodAnswer()
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

    public function testItShould_notShowLessonExercisesViewPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notShowLessonExercisesViewPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/exercises');

        $this->assertResponseForbidden();
    }

    public function testItShould_notShowLessonExercisesViewPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/exercises');

        $this->assertResponseNotFound();
    }

    // edit

    public function testItShould_showLessonEditPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseOk();
        $this->assertEquals($lesson->id, $this->view()->lesson->id);
    }

    public function testItShould_notShowLessonEditPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notShowLessonEditPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/edit');

        $this->assertResponseForbidden();
    }

    public function testItShould_notShowLessonEditPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/edit');

        $this->assertResponseNotFound();
    }

    // update

    public function testItShould_updateLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $input = [
            'visibility' => 'private',
            'name' => uniqid(),
            'bidirectional' => '1',
        ];

        $this->call('PUT', '/lessons/'.$lesson->id, $input);

        /** @var Lesson $lesson */
        $lesson = $lesson->fresh();
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertEquals($input['name'], $lesson->name);
        $this->assertResponseRedirectedTo('/lessons/'.$lesson->id.'/edit');
    }

    public function testItShould_notUpdateLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('PUT', '/lessons/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notUpdateLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('PUT', '/lessons/'.$lesson->id);

        $this->assertResponseForbidden();
    }

    public function testItShould_notUpdateLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('PUT', '/lessons/-1');

        $this->assertResponseNotFound();
    }

    public function testItShould_notUpdateLesson_invalidInput()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('PUT', '/lessons/'.$lesson->id);

        $this->assertResponseInvalidInput();
    }

    // delete

    public function testItShould_deleteLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('DELETE', '/lessons/'.$lesson->id);

        $this->assertResponseRedirectedTo('/home');
        $this->assertNull($lesson->fresh());
    }

    public function testItShould_notDeleteLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('DELETE', '/lessons/'.$lesson->id);

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notDeleteLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('DELETE', '/lessons/'.$lesson->id);

        $this->assertResponseForbidden();
    }

    public function testItShould_notDeleteLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('DELETE', '/lessons/-1');

        $this->assertResponseNotFound();
    }

    // subscribe

    public function testItShould_subscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe');

        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);
        $this->assertResponseRedirectedBack();
    }

    public function testItShould_notSubscribeLesson_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe');

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notSubscribeLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertResponseRedirectedBack();
    }

    public function testItShould_notSubscribeLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/subscribe');

        $this->assertResponseNotFound();
    }

    // unsubscribe

    public function testItShould_unsubscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->call('POST', '/lessons/'.$lesson->id.'/unsubscribe');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertResponseRedirectedBack();
    }

    public function testItShould_notUnsubscribeLesson_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/unsubscribe');

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notUnsubscribeLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/unsubscribe');

        $this->assertResponseRedirectedBack();
    }

    public function testItShould_notUnsubscribeLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/unsubscribe');

        $this->assertResponseNotFound();
    }

    // subscribeAndLearn

    public function testItShould_subscribeAndLearn()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe-and-learn');

        $this->assertCount(1, $user->subscribedLessons);
        $this->assertEquals($lesson->id, $user->subscribedLessons[0]->id);
        $this->assertResponseRedirectedTo('/learn/lessons/'.$lesson->id);
    }

    public function testItShould_notSubscribeAndLearn_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe-and-learn');

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notSubscribeAndLearn_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/'.$lesson->id.'/subscribe-and-learn');

        $this->assertCount(0, $user->subscribedLessons);
        $this->assertResponseRedirectedTo('/learn/lessons/'.$lesson->id);
    }

    public function testItShould_notSubscribeAndLearn_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/subscribe-and-learn');

        $this->assertResponseNotFound();
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

    public function testItShould_notExportCsv_unauthorised()
    {
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/csv');

        $this->assertResponseUnauthorized();
    }

    public function testItShould_notExportCsv_lessonNotFound()
    {
        $user = $this->createUser();
        $this->be($user);

        $this->call('GET', '/lessons/-1/csv');

        $this->assertResponseNotFound();
    }

    // importCsv

    public function testItShould_importCsv()
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

    public function testItShould_notImportCsv_unauthorized()
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

    public function testItShould_notImportCsv_forbidden()
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

    public function testItShould_notImportCsv_lessonNotFound()
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

    public function testItShould_notImportCsv_invalidInput()
    {
        $user = $this->createUser();
        $this->be($user);
        $lesson = $this->createPrivateLesson($user);

        $this->call('POST', '/lessons/'.$lesson->id.'/csv');

        $this->assertResponseInvalidInput();
    }
}
