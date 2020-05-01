<?php

namespace Tests\Unit\Http\Controllers\Web;

use Illuminate\Http\UploadedFile;
use League\Csv\Writer;

class LessonCsvControllerTest extends TestCase
{
    // exportLessonToCsv

    /** @test */
    public function itShould_exportLessonToCsv()
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
    public function itShould_exportLessonToCsv_noResult()
    {
        $user = $this->createUser();
        $this->be($user);
        $lesson = $this->createPrivateLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

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
            0,
            0,
            0,
        ], $first);
    }

    /** @test */
    public function itShould_notExportLessonToCsv_unauthorised()
    {
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/'.$lesson->id.'/csv');

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notExportLessonToCsv_lessonNotFound()
    {
        $user = $this->createUser();
        $this->be($user);

        $this->call('GET', '/lessons/-1/csv');

        $this->assertResponseNotFound();
    }

    // importLessonFromCsv

    /** @test */
    public function itShould_importLessonFromCsv()
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

        $exercise = $lesson->exercises->first();
        $this->assertEquals($data[0], $exercise->question);
        $this->assertEquals($data[1], $exercise->answer);
        $this->assertEquals($lesson->id, $exercise->lesson_id);
        $this->assertEquals(2, $this->numberOfGoodAnswers($exercise, $user->id));
        $this->assertEquals(8, $this->numberOfBadAnswers($exercise, $user->id));
        $this->assertEquals(80, $this->percentOfGoodAnswersOfExercise($exercise, $user->id));
    }

    /** @test */
    public function itShould_notImportLessonFromCsv_unauthorized()
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
    public function itShould_notImportLessonFromCsv_forbidden()
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
    public function itShould_notImportLessonFromCsv_lessonNotFound()
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
    public function itShould_notImportLessonFromCsv_invalidInput()
    {
        $user = $this->createUser();
        $this->be($user);
        $lesson = $this->createPrivateLesson($user);

        $this->call('POST', '/lessons/'.$lesson->id.'/csv');

        $this->assertResponseInvalidInput();
    }
}
