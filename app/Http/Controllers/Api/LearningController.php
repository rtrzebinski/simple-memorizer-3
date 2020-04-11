<?php

namespace App\Http\Controllers\Api;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Http\Requests\FetchRandomExerciseOfLessonRequest;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\LearningService;
use App\Structures\UserLessonRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;

class LearningController extends Controller
{
    /**
     * @param FetchRandomExerciseOfLessonRequest $request
     * @param LearningService                    $learningService
     * @param UserLessonRepositoryInterface      $userLessonRepository
     * @param Lesson                             $lesson
     * @return JsonResponse
     * @throws Exception
     */
    public function fetchRandomExerciseOfLesson(
        FetchRandomExerciseOfLessonRequest $request,
        LearningService $learningService,
        UserLessonRepositoryInterface $userLessonRepository,
        Lesson $lesson
    ): JsonResponse {
        $userLesson = $userLessonRepository->fetchUserLesson($lesson->id);
        $userExercise = $learningService->fetchRandomExerciseOfLesson($userLesson, $this->user(), $request->previous_exercise_id);
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
