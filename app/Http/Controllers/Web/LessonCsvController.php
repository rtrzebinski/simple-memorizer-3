<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\LessonImportCsvRequest;
use App\Models\Exercise;
use App\Models\ExerciseResult;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class LessonCsvController extends Controller
{
    /**
     * @param Lesson $lesson
     * @return Response
     */
    public function exportLessonToCsv(Lesson $lesson): Response
    {
        $writer = $this->createCsvWriter();

        $writer->insertOne([
            "question",
            "answer",
            "number_of_good_answers",
            "number_of_bad_answers",
            "percent_of_good_answers"
        ]);

        foreach ($lesson->exercises as $exercise) {
            /** @var ExerciseResult $exerciseResult */
            $exerciseResult = $exercise->results()->where('exercise_results.user_id', $this->user()->id)->first();

            $writer->insertOne([
                'question' => $exercise->question,
                'answer' => $exercise->answer,
                'number_of_good_answers' => $exerciseResult->number_of_good_answers ?? 0,
                'number_of_bad_answers' => $exerciseResult->number_of_bad_answers ?? 0,
                'percent_of_good_answers' => $exerciseResult->percent_of_good_answers ?? 0,
            ]);
        }

        $filename = $lesson->name.'.csv';

        return response((string)$writer, 200)
            ->header('Content-type', 'application/force-download')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    /**
     * @param Lesson                 $lesson
     * @param LessonImportCsvRequest $request
     * @return RedirectResponse
     */
    public function importLessonFromCsv(Lesson $lesson, LessonImportCsvRequest $request): RedirectResponse
    {
        $reader = Reader::createFromPath($request->file('csv_file')->getRealPath());

        foreach ($reader as $index => $row) {
            if ($index == 0) {
                // skip header line
                continue;
            }

            $exercise = new Exercise([
                'question' => $row[0],
                'answer' => $row[1],
                'lesson_id' => $lesson->id,
            ]);
            $exercise->lesson_id = $lesson->id;
            $exercise->save();

            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $this->user()->id;
            $exerciseResult->exercise_id = $exercise->id;
            $exerciseResult->number_of_good_answers = $row['2'];
            $exerciseResult->number_of_bad_answers = $row['3'];
            $exerciseResult->percent_of_good_answers = $row['4'];
            $exerciseResult->save();
        }

        return redirect('/lessons/'.$lesson->id);
    }

    /**
     * @return Writer
     */
    private function createCsvWriter(): Writer
    {
        //the CSV file will be created using a temporary File
        $writer = Writer::createFromFileObject(new SplTempFileObject);
        //the delimiter will be the tab character
        $writer->setDelimiter(",");
        //use windows line endings for compatibility with some csv libraries
        $writer->setNewline("\r\n");
        return $writer;
    }
}
