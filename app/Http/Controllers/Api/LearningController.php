<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotEnoughExercisesException;
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
        try {
            $exercise = $learningService->fetchRandomExerciseOfLesson($lesson, $this->user()->id, $request->previous_exercise_id);
            return $this->response($exercise);
        } catch (NotEnoughExercisesException $e) {
            return $this->response('', NotEnoughExercisesException::HTTP_RESPONSE_CODE);
        }
    }

    /**
     * @param Exercise        $exercise
     * @param LearningService $learningService
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handleGoodAnswer(Exercise $exercise, LearningService $learningService)
    {
        $this->authorizeForUser($this->user(), 'access', $exercise);
        $learningService->handleGoodAnswer($exercise->id, $this->user()->id);
    }

    /**
     * @param Exercise        $exercise
     * @param LearningService $learningService
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handleBadAnswer(Exercise $exercise, LearningService $learningService)
    {
        $this->authorizeForUser($this->user(), 'access', $exercise);
        $learningService->handleBadAnswer($exercise->id, $this->user()->id);
    }
}
