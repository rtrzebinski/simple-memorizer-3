<?php

namespace App\Http\Controllers\Web;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Services\LearningService;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\View\View;

class LearnFavouritesWebController extends WebController
{
    /**
     * @param Request $request
     * @param LearningService $learningService
     * @param AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
     * @return View|Response
     */
    public function learnFavourites(
        Request $request,
        LearningService $learningService,
        AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
    ) {
        if ($requestedExerciseId = $request->get('requested_exercise_id')) {
            $userExercise = $userExerciseRepository->fetchUserExerciseOfExercise($requestedExerciseId);
            // ensure user can access this exercise
            $this->authorizeForUser($this->user(), 'access', $userExercise);
        } else {
            $userExercises = $userExerciseRepository->fetchUserExercisesOfFavouriteLessons();
            $userExercise = $learningService->findUserExerciseToLearn($userExercises, $request->previous_exercise_id);
        }

        if ($userExercise) {
            $redirectUrl = '/learn/favourites/?requested_exercise_id=' . $userExercise->exercise_id;
            $editExerciseUrl = URL::to(
                '/exercises/' . $userExercise->exercise_id . '/edit?hide_lesson=true&redirect_to=' . urlencode(
                    $redirectUrl
                )
            );
        }

        if ($userExercise) {
            $canEditExercise = $userExercise->lesson_owner_id == $this->user()->id;
        }

        return view(
            'learn.favourites',
            [
                'userExercise' => $userExercise,
                'canEditExercise' => $canEditExercise ?? null,
                'editExerciseUrl' => $editExerciseUrl ?? null,
            ]
        );
    }

    /**
     * @param Request $request
     * @param LearningService $learningService
     * @param AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
     * @return Response|View
     */
    public function handleAnswer(
        Request $request,
        LearningService $learningService,
        AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
    ) {
        $this->validate(
            $request,
            [
                'answer' => 'required|in:good,bad',
                'previous_exercise_id' => 'required|int',
            ]
        );

        $previousExerciseId = $request->previous_exercise_id;

        match ($request->answer) {
            'good' => event(new ExerciseGoodAnswer($previousExerciseId, $this->user())),
            'bad' => event(new ExerciseBadAnswer($previousExerciseId, $this->user())),
        };

        return $this->learnFavourites($request, $learningService, $userExerciseRepository);
    }
}
