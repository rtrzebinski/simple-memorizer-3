<?php

namespace App\Http\Controllers\Api;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Http\Requests\FetchRandomExerciseOfLessonRequest;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\LearningService;
use App\Services\UserExerciseModifier;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;

class LearnLessonApiController extends ApiController
{
    /**
     * @param Lesson $lesson
     * @param FetchRandomExerciseOfLessonRequest $request
     * @param AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
     * @param LearningService $learningService
     * @param AuthenticatedUserLessonRepositoryInterface $userLessonRepository
     * @param UserExerciseModifier $userExerciseModifier
     * @return JsonResponse
     * @throws Exception
     */
    public function fetchRandomExerciseOfLesson(
        Lesson $lesson,
        FetchRandomExerciseOfLessonRequest $request,
        AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository,
        LearningService $learningService,
        AuthenticatedUserLessonRepositoryInterface $userLessonRepository,
        UserExerciseModifier $userExerciseModifier
    ): JsonResponse {
        $userExercises = $userExerciseRepository->fetchUserExercisesOfLesson($lesson->id);

        $userExercise = $learningService->findUserExerciseToLearn($userExercises, $request->previous_exercise_id);

        $userLesson = $userLessonRepository->fetchUserLesson($lesson->id);

        if ($userExercise && $userLesson->is_bidirectional) {
            // if lesson is bidirectional swap question and answer with 50% chance
            $userExercise = $userExerciseModifier->swapQuestionWithAnswer($userExercise, $probability = 50);
        }

        return $this->response($userExercise);
    }

    /**
     * @param Exercise $exercise
     */
    public function handleGoodAnswer(Exercise $exercise)
    {
        event(new ExerciseGoodAnswer($exercise->id, $this->user()));
    }

    /**
     * @param Exercise $exercise
     */
    public function handleBadAnswer(Exercise $exercise)
    {
        event(new ExerciseBadAnswer($exercise->id, $this->user()));
    }
}
