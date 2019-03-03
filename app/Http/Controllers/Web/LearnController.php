<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\NotEnoughExercisesException;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise\Exercise;
use App\Models\Lesson\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearnController extends Controller
{
    /**
     * @param Lesson  $lesson
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|View
     * @throws NotEnoughExercisesException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function learnLesson(Lesson $lesson, Request $request)
    {
        $this->authorizeForUser($this->user(), 'learn', $lesson);
        $requestedExerciseId = $request->get('requested_exercise_id');

        if ($requestedExerciseId) {
            $exercise = Exercise::findOrFail($requestedExerciseId);
            $this->authorizeForUser($this->user(), 'access', $exercise);
        } else {
            $previousExerciseId = $request->get('previous_exercise_id');
            $exercise = $lesson->fetchRandomExercise($this->user()->id, $previousExerciseId);
        }

        return view('learn.learn', compact('lesson', 'exercise'));
    }

    /**
     * @param Exercise $exercise
     * @param int      $lessonId
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handleGoodAnswer(Exercise $exercise, int $lessonId): RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'learn', $exercise->lesson);

        $exercise->handleGoodAnswer($this->user()->id);

        return redirect('/learn/lessons/'.$lessonId.'?previous_exercise_id='.$exercise->id);
    }

    /**
     * @param Exercise $exercise
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handleBadAnswer(Exercise $exercise)
    {
        $this->authorizeForUser($this->user(), 'learn', $exercise->lesson);

        $exercise->handleBadAnswer($this->user()->id);
    }

    /**
     * @param Exercise              $exercise
     * @param int                   $lessonId
     * @param UpdateExerciseRequest $request
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateExercise(Exercise $exercise, int $lessonId, UpdateExerciseRequest $request): RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $exercise);

        $exercise->update($request->all());

        return redirect('/learn/lessons/'.$lessonId.'?requested_exercise_id='.$exercise->id);
    }
}
