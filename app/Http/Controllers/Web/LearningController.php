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
     * @return View
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
     * @param int                    $lessonId
     * @param Request                $request
     * @param LearningService        $learningService
     * @param UserExerciseRepository $userExerciseRepository
     * @param UserLessonRepository   $userLessonRepository
     * @return View
     * @throws \Exception
     */
    public function handleAnswer(int $lessonId, Request $request, LearningService $learningService, UserExerciseRepository $userExerciseRepository, UserLessonRepository $userLessonRepository)
    {
        $this->validate($request, [
            'answer' => 'required|in:good,bad',
            'previous_exercise_id' => 'required|int',
        ]);

        $previousExerciseId = $request->previous_exercise_id;

        if ($request->answer == 'good') {
            event(new ExerciseGoodAnswer($previousExerciseId, $this->user()));
        }

        if ($request->answer == 'bad') {
            event(new ExerciseBadAnswer($previousExerciseId, $this->user()));
        }

        return $this->learnLesson($lessonId, $request, $learningService, $userExerciseRepository, $userLessonRepository);
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
