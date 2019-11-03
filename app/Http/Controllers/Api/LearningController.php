<?php

namespace App\Http\Controllers\Api;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Http\Requests\FetchRandomExerciseOfLessonRequest;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\LearningService;
use Exception;
use Illuminate\Http\JsonResponse;

class LearningController extends Controller
{
    /**
     * @param FetchRandomExerciseOfLessonRequest $request
     * @param LearningService                    $learningService
     * @param Lesson                             $lesson
     * @return JsonResponse
     * @throws Exception
     */
    public function fetchRandomExerciseOfLesson(
        FetchRandomExerciseOfLessonRequest $request,
        LearningService $learningService,
        Lesson $lesson
    ): JsonResponse {
        $userExercise = $learningService->fetchRandomExerciseOfLesson($lesson, $this->user(), $request->previous_exercise_id);
        return $this->response($userExercise);
    }

    /**
     * @param Exercise $exercise
     */
    public function handleGoodAnswer(Exercise $exercise)
    {
        event(new ExerciseGoodAnswer($exercise, $this->user()));
    }

    /**
     * @param Exercise $exercise
     */
    public function handleBadAnswer(Exercise $exercise)
    {
        event(new ExerciseBadAnswer($exercise, $this->user()));
    }
}
