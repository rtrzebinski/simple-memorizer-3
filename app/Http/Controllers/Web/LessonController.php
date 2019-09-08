<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\LessonImportCsvRequest;
use App\Models\Exercise;
use App\Models\ExerciseResult;
use App\Models\Lesson;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use League\Csv\Reader;

class LessonController extends Controller
{
    /**
     * @return View
     */
    public function create() : View
    {
        return view('lessons.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'visibility' => 'required|in:public,private',
            'name' => 'required|string',
            'bidirectional' => 'required|boolean',
        ]);

        $lesson = new Lesson($request->all());
        $lesson->owner_id = $this->user()->id;
        $lesson->save();

        return redirect('/lessons/' . $lesson->id);
    }

    /**
     * @param Lesson $lesson
     * @return mixed
     */
    public function view(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);
        return view('lessons.view', compact('lesson'));
    }

    /**
     * @param Lesson $lesson
     * @return View
     */
    public function edit(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);
        return view('lessons.edit', compact('lesson'));
    }

    /**
     * @param Request $request
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function update(Request $request, Lesson $lesson) : RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);

        $this->validate($request, [
            'visibility' => 'required|in:public,private',
            'name' => 'required|string',
            'bidirectional' => 'required|boolean',
        ]);

        $lesson->update($request->all());
        return redirect('/lessons/' . $lesson->id . '/edit');
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(Lesson $lesson) : RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);
        $lesson->delete();
        return redirect('/home');
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function subscribe(Lesson $lesson) : RedirectResponse
    {
        if (Gate::forUser($this->user())->allows('subscribe', $lesson)) {
            $lesson->subscribe($this->user());
        }
        return back();
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function unsubscribe(Lesson $lesson) : RedirectResponse
    {
        if (Gate::forUser($this->user())->allows('unsubscribe', $lesson)) {
            $lesson->unsubscribe($this->user());
        }
        return back();
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function subscribeAndLearn(Lesson $lesson) :  RedirectResponse
    {
        if (Gate::forUser($this->user())->allows('subscribe', $lesson)) {
            $lesson->subscribe($this->user());
        }
        return redirect('/learn/lessons/' . $lesson->id);
    }

    /**
     * @param Lesson $lesson
     * @return Response
     */
    public function exportCsv(Lesson $lesson) : Response
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
            $exerciseResult = $exercise->resultOfUser($this->user()->id);

            $writer->insertOne([
                'question' => $exercise->question,
                'answer' => $exercise->answer,
                'number_of_good_answers' => $exerciseResult->number_of_good_answers ?? 0,
                'number_of_bad_answers' => $exerciseResult->number_of_bad_answers ?? 0,
                'percent_of_good_answers' => $exerciseResult->percent_of_good_answers ?? 0,
            ]);
        }

        $filename = $lesson->name . '.csv';

        return response((string)$writer, 200)
            ->header('Content-type', 'application/force-download')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * @param Lesson $lesson
     * @param LessonImportCsvRequest $request
     * @return RedirectResponse
     */
    public function importCsv(Lesson $lesson, LessonImportCsvRequest $request) : RedirectResponse
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

        return redirect('/lessons/' . $lesson->id);
    }
}
