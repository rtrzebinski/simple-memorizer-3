<?php

namespace App\Http\Controllers\Web;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Services\LearningService;
use App\Structures\UserExerciseRepositoryInterface;
use App\Structures\UserLessonRepositoryInterface;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LearningController extends Controller
{
    /**
     * @param int                             $lessonId
     * @param Request                         $request
     * @param LearningService                 $learningService
     * @param UserExerciseRepositoryInterface $userExerciseRepository
     * @param UserLessonRepositoryInterface   $userLessonRepository
     * @return View|Response
     * @throws Exception
     */
    public function learnLesson(int $lessonId, Request $request, LearningService $learningService, UserExerciseRepositoryInterface $userExerciseRepository, UserLessonRepositoryInterface $userLessonRepository)
    {
        $userLesson = $userLessonRepository->fetchUserLesson($this->user(), $lessonId);

        // lesson does not exist
        if (!$userLesson) {
            return response('Not Found', Response::HTTP_NOT_FOUND);
        }

        // user does not subscribe lesson
        if (!$userLesson->is_subscriber) {
            return response('This action is unauthorized', Response::HTTP_FORBIDDEN);
        }

        if ($requestedExerciseId = $request->get('requested_exercise_id')) {
            $userExercise = $userExerciseRepository->fetchUserExerciseOfExercise($this->user(), $requestedExerciseId);
            // ensure user can access this exercise
            $this->authorizeForUser($this->user(), 'access', $userExercise);
        } else {
            $userExercise = $learningService->fetchRandomExerciseOfLesson($userLesson, $this->user(), $request->get('previous_exercise_id'));
        }

        return view('learn.learn', [
            'userLesson' => $userLesson,
            'userExercise' => $userExercise,
            'canModifyExercise' => $userLesson->owner_id == $this->user()->id,
        ]);
    }

    /**
     * @param int                             $lessonId
     * @param Request                         $request
     * @param LearningService                 $learningService
     * @param UserExerciseRepositoryInterface $userExerciseRepository
     * @param UserLessonRepositoryInterface   $userLessonRepository
     * @return View
     * @throws Exception
     */
    public function handleAnswer(int $lessonId, Request $request, LearningService $learningService, UserExerciseRepositoryInterface $userExerciseRepository, UserLessonRepositoryInterface $userLessonRepository)
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
