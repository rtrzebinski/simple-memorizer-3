<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\NotEnoughExercisesException;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise\Exercise;
use App\Models\Lesson\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class LearnController extends Controller
{
    /**
     * @param Lesson $lesson
     * @param Request $request
     * @return RedirectResponse|View
     * @throws NotEnoughExercisesException
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
     * @return RedirectResponse
     */
    public function handleGoodAnswer(Exercise $exercise) : RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'learn', $exercise->lesson);

        $exercise->handleGoodAnswer($this->user()->id);

        return redirect('/learn/lessons/' . $exercise->lesson_id . '?previous_exercise_id=' . $exercise->id);
    }

    /**
     * @param Exercise $exercise
     */
    public function handleBadAnswer(Exercise $exercise)
    {
        $this->authorizeForUser($this->user(), 'learn', $exercise->lesson);

        $exercise->handleBadAnswer($this->user()->id);
    }

    /**
     * @param Exercise $exercise
     * @param UpdateExerciseRequest $request
     * @return RedirectResponse
     */
    public function updateExercise(Exercise $exercise, UpdateExerciseRequest $request) : RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $exercise);

        $exercise->update($request->all());

        return redirect('/learn/lessons/' . $exercise->lesson_id . '?requested_exercise_id=' . $exercise->id);
    }
}
