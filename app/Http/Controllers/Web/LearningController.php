<?php

namespace App\Http\Controllers\Web;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\LearningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearningController extends Controller
{
    /**
     * @param Lesson          $lesson
     * @param Request         $request
     * @param LearningService $learningService
     * @return \Illuminate\Contracts\View\Factory|View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function learnLesson(Lesson $lesson, Request $request, LearningService $learningService)
    {
        $requestedExerciseId = $request->get('requested_exercise_id');

        if ($requestedExerciseId) {
            $exercise = Exercise::findOrFail($requestedExerciseId);
            $this->authorizeForUser($this->user(), 'access', $exercise);
        } else {
            $previousExerciseId = $request->get('previous_exercise_id');
            $exercise = $learningService->fetchRandomExerciseOfLesson($lesson, $this->user()->id, $previousExerciseId);
        }

        return view('learn.learn', [
            'lesson' => $lesson,
            'exercise' => $exercise,
        ]);
    }

    /**
     * @param Exercise $exercise
     * @param int      $lessonId
     * @return RedirectResponse
     */
    public function handleGoodAnswer(Exercise $exercise, int $lessonId): RedirectResponse
    {
        event(new ExerciseGoodAnswer($exercise, $this->user()));

        return redirect('/learn/lessons/'.$lessonId.'?previous_exercise_id='.$exercise->id);
    }

    /**
     * @param Exercise $exercise
     * @param int      $lessonId
     * @return RedirectResponse
     */
    public function handleBadAnswer(Exercise $exercise, int $lessonId)
    {
        event(new ExerciseBadAnswer($exercise, $this->user()));

        return redirect('/learn/lessons/'.$lessonId.'?previous_exercise_id='.$exercise->id);
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
