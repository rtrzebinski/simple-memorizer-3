<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\LessonImportCsvRequest;
use App\Models\Exercise;
use App\Models\ExerciseResult;
use App\Models\Lesson;
use App\Structures\UserExercise\UserExerciseRepositoryInterface;
use App\Structures\UserLesson\UserLessonRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
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
     * @throws ValidationException
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
     * @param int                           $lessonId
     * @param UserLessonRepositoryInterface $userLessonRepository
     * @return View
     * @throws AuthorizationException
     */
    public function view(int $lessonId, UserLessonRepositoryInterface $userLessonRepository): View
    {
        $userLesson = $userLessonRepository->fetchUserLesson($lessonId);

        $this->authorizeForUser($this->user(), 'access', $userLesson);

        return view('lessons.view', $this->lessonViewData($userLesson));
    }

    /**
     * @param Lesson                          $lesson
     * @param UserLessonRepositoryInterface   $userLessonRepository
     * @param UserExerciseRepositoryInterface $userExerciseRepository
     * @return mixed
     * @throws AuthorizationException
     */
    public function exercises(Lesson $lesson, UserLessonRepositoryInterface $userLessonRepository, UserExerciseRepositoryInterface $userExerciseRepository): View
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);

        $userExercises = $userExerciseRepository->fetchUserExercisesOfLesson($lesson->id);

        $userLesson = $userLessonRepository->fetchUserLesson($lesson->id);

        $data = [
                'canModifyLesson' => Gate::forUser($this->user())->allows('modify', $lesson),
                'userExercises' => $userExercises,
            ] + $this->lessonViewData($userLesson);

        return view('lessons.exercises', $data);
    }

    /**
     * @param int                           $lessonId
     * @param UserLessonRepositoryInterface $userLessonRepository
     * @return View
     * @throws AuthorizationException
     */
    public function edit(int $lessonId, UserLessonRepositoryInterface $userLessonRepository): View
    {
        $userLesson = $userLessonRepository->fetchUserLesson($lessonId);

        $this->authorizeForUser($this->user(), 'modify', $userLesson);

        return view('lessons.edit', $this->lessonViewData($userLesson));
    }

    /**
     * @param Request $request
     * @param Lesson  $lesson
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws ValidationException
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
     * @param int                           $lessonId
     * @param UserLessonRepositoryInterface $userLessonRepository
     * @return View|Response
     */
    public function settings(int $lessonId, UserLessonRepositoryInterface $userLessonRepository)
    {
        $userLesson = $userLessonRepository->fetchUserLesson($lessonId);

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
     * @throws ValidationException
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
