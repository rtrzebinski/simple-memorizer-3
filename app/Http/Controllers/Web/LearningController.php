<?php

namespace App\Http\Controllers\Web;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Services\LearningService;
use App\Structures\UserExerciseRepository;
use App\Structures\UserLessonRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LearningController extends Controller
{
    /**
     * @param int                    $lessonId
     * @param Request                $request
     * @param LearningService        $learningService
     * @param UserExerciseRepository $userExerciseRepository
     * @param UserLessonRepository   $userLessonRepository
     * @return \Illuminate\Contracts\View\Factory|View
     * @throws \Exception
     */
    public function learnLesson(int $lessonId, Request $request, LearningService $learningService, UserExerciseRepository $userExerciseRepository, UserLessonRepository $userLessonRepository)
    {
        $userLesson = $userLessonRepository->fetchUserLesson($this->user(), $lessonId);

        if (!$userLesson) {
            // user does not subscribe lesson
            return response('This action is unauthorized', Response::HTTP_FORBIDDEN);
        }

        $requestedExerciseId = $request->get('requested_exercise_id');

        if ($requestedExerciseId) {
            $userExercise = $userExerciseRepository->fetchUserExerciseOfExercise($this->user(), $requestedExerciseId);
            // todo create gate for UserExercise and fix skipped test
            // we'll need to check if user exercise belongs to a lesson or lesson child
//            $this->authorizeForUser($this->user(), 'access', $exercise);
        } else {
            $previousExerciseId = $request->get('previous_exercise_id');
            $userExercise = $learningService->fetchRandomExerciseOfLesson($userLesson, $this->user(), $previousExerciseId);
        }

        return view('learn.learn', [
            'userLesson' => $userLesson,
            'userExercise' => $userExercise,
            'canModifyExercise' => $userLesson->owner_id == $this->user()->id,
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
