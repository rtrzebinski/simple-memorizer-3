<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\LessonImportCsvRequest;
use App\Models\Exercise;
use App\Models\ExerciseResult;
use App\Models\Lesson;
use App\Structures\UserLessonRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
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
    public function create(): View
    {
        return view('lessons.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'visibility' => 'required|in:public,private',
            'name' => 'required|string',
        ]);

        $lesson = new Lesson($request->all());
        $lesson->owner_id = $this->user()->id;
        $lesson->save();

        // always subscribe owned lesson
        $lesson->subscribe($this->user());

        return redirect('/lessons/'.$lesson->id);
    }

    /**
     * @param int                  $lessonId
     * @param UserLessonRepository $userLessonRepository
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function view(int $lessonId, UserLessonRepository $userLessonRepository): View
    {
        $userLesson = $userLessonRepository->fetchUserLesson($this->user(), $lessonId);

        $this->authorizeForUser($this->user(), 'access', $userLesson);

        return view('lessons.view', $this->lessonViewData($userLesson));
    }

    /**
     * @param Lesson               $lesson
     * @param UserLessonRepository $userLessonRepository
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function exercises(Lesson $lesson, UserLessonRepository $userLessonRepository): View
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);

        // will include exercises from aggregated lessons
        $exercises = $lesson->allExercises();

        if ($this->user()) {
            // eager load exercise results of current user only
            $exercises->load([
                'results' => function (Relation $relation) {
                    $relation->where('exercise_results.user_id', $this->user()->id);
                }
            ]);

            // load percent_of_good_answers property using eager loaded 'results' relationship
            foreach ($exercises as $exercise) {
                // only current user results were eager loaded above, so we don't need any more filtering here
                $exercise->percent_of_good_answers = $exercise->results->first()->percent_of_good_answers ?? 0;
            }

        }

        $userLesson = $userLessonRepository->fetchUserLesson($this->user(), $lesson->id);

        $data = [
                'canModifyLesson' => Gate::forUser($this->user())->allows('modify', $lesson),
                'exercises' => $exercises,
            ] + $this->lessonViewData($userLesson);

        return view('lessons.exercises', $data);
    }

    /**
     * @param int                  $lessonId
     * @param UserLessonRepository $userLessonRepository
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(int $lessonId, UserLessonRepository $userLessonRepository): View
    {
        $userLesson = $userLessonRepository->fetchUserLesson($this->user(), $lessonId);

        $this->authorizeForUser($this->user(), 'modify', $userLesson);

        return view('lessons.edit', $this->lessonViewData($userLesson));
    }

    /**
     * @param Request $request
     * @param Lesson  $lesson
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function saveEdit(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);

        $this->validate($request, [
            'visibility' => 'required|in:public,private',
            'name' => 'required|string',
        ]);

        $lesson->update($request->all());
        return redirect('/lessons/'.$lesson->id.'/edit');
    }

    /**
     * @param int                  $lessonId
     * @param UserLessonRepository $userLessonRepository
     * @return View|RedirectResponse
     */
    public function settings(int $lessonId, UserLessonRepository $userLessonRepository)
    {
        $userLesson = $userLessonRepository->fetchUserLesson($this->user(), $lessonId);

        // user does not subscribe lesson
        if (!$userLesson->is_subscriber) {
            return response('This action is unauthorized', Response::HTTP_FORBIDDEN);
        }

        return view('lessons.settings', $this->lessonViewData($userLesson));
    }

    /**
     * @param Request $request
     * @param Lesson  $lesson
     * @return RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function saveSettings(Request $request, Lesson $lesson): RedirectResponse
    {
        if (!Gate::forUser($this->user())->denies('subscribe', $lesson)) {
            return redirect('/lessons/'.$lesson->id);
        }

        $this->validate($request, [
            'bidirectional' => 'required|boolean',
        ]);

        $pivot = $lesson->subscriberPivot($this->user()->id);
        $pivot->update([
            'bidirectional' => $request->bidirectional,
        ]);

        return redirect('/lessons/'.$lesson->id.'/settings');
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(Lesson $lesson): RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);
        $lesson->delete();
        return redirect('/home');
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function subscribe(Lesson $lesson): RedirectResponse
    {
        if (Gate::forUser($this->user())->allows('subscribe', $lesson)) {
            $lesson->subscribe($this->user());
        }
        return back();
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     * @throws \Exception
     */
    public function unsubscribe(Lesson $lesson): RedirectResponse
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
    public function subscribeAndLearn(Lesson $lesson): RedirectResponse
    {
        if (Gate::forUser($this->user())->allows('subscribe', $lesson)) {
            $lesson->subscribe($this->user());
        }
        return redirect('/learn/lessons/'.$lesson->id);
    }

    /**
     * @param Lesson $lesson
     * @return Response
     */
    public function exportCsv(Lesson $lesson): Response
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
    public function importCsv(Lesson $lesson, LessonImportCsvRequest $request): RedirectResponse
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
}
