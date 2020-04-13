<?php

namespace App\Http\Controllers\Web;

use App\Events\ExerciseCreated;
use App\Events\ExerciseDeleted;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Structures\UserLesson\UserLessonRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    /**
     * @param int                           $lessonId
     * @param UserLessonRepositoryInterface $userLessonRepository
     * @return View
     * @throws AuthorizationException
     */
    public function create(int $lessonId, UserLessonRepositoryInterface $userLessonRepository): View
    {
        $userLesson = $userLessonRepository->fetchUserLesson($lessonId);

        $this->authorizeForUser($this->user(), 'modify', $userLesson);

        return view('exercises.create', $this->lessonViewData($userLesson));
    }

    /**
     * @param Lesson               $lesson
     * @param StoreExerciseRequest $request
     * @return RedirectResponse
     */
    public function store(Lesson $lesson, StoreExerciseRequest $request): RedirectResponse
    {
        $exercise = new Exercise($request->all());
        $exercise->lesson_id = $lesson->id;
        $exercise->save();

        event(new ExerciseCreated($lesson, $this->user()));

        return redirect('/lessons/'.$lesson->id.'/exercises');
    }

    /**
     * @param Exercise                      $exercise
     * @param UserLessonRepositoryInterface $userLessonRepository
     * @return View
     * @throws AuthorizationException
     */
    public function edit(Exercise $exercise, UserLessonRepositoryInterface $userLessonRepository): View
    {
        $this->authorizeForUser($this->user(), 'modify', $exercise);

        $userLesson = $userLessonRepository->fetchUserLesson($exercise->lesson_id);

        return view('exercises.edit', [
                'exercise' => $exercise,
            ] + $this->lessonViewData($userLesson));
    }

    /**
     * @param Exercise              $exercise
     * @param UpdateExerciseRequest $request
     * @return RedirectResponse
     */
    public function update(Exercise $exercise, UpdateExerciseRequest $request): RedirectResponse
    {
        $exercise->update($request->only(['question', 'answer']));

        return redirect($request->redirect_to);
    }

    /**
     * @param Exercise $exercise
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(Exercise $exercise): RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $exercise);

        $exercise->delete();

        event(new ExerciseDeleted($exercise->lesson, $this->user()));

        return redirect(url()->previous());
    }
}
