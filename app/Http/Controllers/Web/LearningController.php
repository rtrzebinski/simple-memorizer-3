<?php

namespace App\Http\Controllers\Web;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\LearningService;
use App\Structures\UserExerciseRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearningController extends Controller
{
    /**
     * @param Lesson                 $lesson
     * @param Request                $request
     * @param LearningService        $learningService
     * @param UserExerciseRepository $userExerciseRepository
     * @return \Illuminate\Contracts\View\Factory|View
     * @throws \Exception
     */
    public function learnLesson(Lesson $lesson, Request $request, LearningService $learningService, UserExerciseRepository $userExerciseRepository)
    {
        $requestedExerciseId = $request->get('requested_exercise_id');

        if ($requestedExerciseId) {
            $userExercise = $userExerciseRepository->fetchUserExerciseOfExercise($this->user(), $requestedExerciseId);
            // todo create gate for UserExercise and fix skipped test
            // we'll need to check if user exercise belongs to a lesson or lesson child
//            $this->authorizeForUser($this->user(), 'access', $exercise);
        } else {
            $previousExerciseId = $request->get('previous_exercise_id');
            $userExercise = $learningService->fetchRandomExerciseOfLesson($lesson, $this->user(), $previousExerciseId);
        }

        return view('learn.learn', [
            'lesson' => $lesson,
            'userExercise' => $userExercise,
            'canModifyExercise' => $lesson->owner_id == $this->user()->id,
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
