<?php

namespace App\Http\Controllers\Web;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Services\LearningService;
use App\Services\UserExerciseModifier;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class LearnLessonController extends Controller
{
    /**
     * @param int                                          $lessonId
     * @param Request                                      $request
     * @param LearningService                              $learningService
     * @param AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
     * @param AuthenticatedUserLessonRepositoryInterface   $userLessonRepository
     * @param UserExerciseModifier                         $userExerciseModifier
     * @return View|Response
     */
    public function learnLesson(
        int $lessonId,
        Request $request,
        LearningService $learningService,
        AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository,
        AuthenticatedUserLessonRepositoryInterface $userLessonRepository,
        UserExerciseModifier $userExerciseModifier
    ) {
        $userLesson = $userLessonRepository->fetchUserLesson($lessonId);

        // lesson does not exist
        if (!$userLesson) {
            return response('Not Found', Response::HTTP_NOT_FOUND);
        }

        // user does not subscribe lesson
        if (!$userLesson->is_subscriber) {
            return response('This action is unauthorized', Response::HTTP_FORBIDDEN);
        }

        if ($requestedExerciseId = $request->get('requested_exercise_id')) {
            $userExercise = $userExerciseRepository->fetchUserExerciseOfExercise($requestedExerciseId);
            // ensure user can access this exercise
            $this->authorizeForUser($this->user(), 'access', $userExercise);
        } else {
            $userExercises = $userExerciseRepository->fetchUserExercisesOfLesson($lessonId);
            $userExercise = $learningService->findUserExerciseToLearn($userExercises, $request->get('previous_exercise_id'));
        }

        if ($userExercise && $userLesson->is_bidirectional) {
            // if lesson is bidirectional swap question and answer with 50% chance
            $userExercise = $userExerciseModifier->swapQuestionWithAnswer($userExercise, $probability = 50);
        }

        $redirectUrl = '/learn/lessons/'.$lessonId.'?requested_exercise_id='.$userExercise->exercise_id;
        $editExerciseUrl = URL::to('/exercises/'.$userExercise->exercise_id.'/edit?hide_lesson=true&redirect_to='.urlencode($redirectUrl));

        $canEditExercise = $userExercise->lesson_owner_id == $this->user()->id;

        return view('learn.lesson', [
            'userLesson' => $userLesson,
            'userExercise' => $userExercise,
            'canEditExercise' => $canEditExercise,
            'editExerciseUrl' => $editExerciseUrl,
        ]);
    }

    /**
     * @param int                                          $lessonId
     * @param Request                                      $request
     * @param LearningService                              $learningService
     * @param AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
     * @param AuthenticatedUserLessonRepositoryInterface   $userLessonRepository
     * @param UserExerciseModifier                         $userExerciseModifier
     * @return View
     */
    public function handleAnswer(
        int $lessonId,
        Request $request,
        LearningService $learningService,
        AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository,
        AuthenticatedUserLessonRepositoryInterface $userLessonRepository,
        UserExerciseModifier $userExerciseModifier
    ) {
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

        return $this->learnLesson($lessonId, $request, $learningService, $userExerciseRepository, $userLessonRepository, $userExerciseModifier);
    }
}
