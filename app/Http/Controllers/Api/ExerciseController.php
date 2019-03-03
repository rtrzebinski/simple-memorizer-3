<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotEnoughExercisesException;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\FetchRandomExerciseOfLessonRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\LearningService;
use Exception;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    /**
     * @param StoreExerciseRequest $request
     * @param Lesson               $lesson
     * @return JsonResponse
     */
    public function storeExercise(StoreExerciseRequest $request, Lesson $lesson): JsonResponse
    {
        $exercise = new Exercise($request->all());
        $exercise->lesson_id = $lesson->id;
        $exercise->save();

        return $this->response($exercise);
    }

    /**
     * @param Exercise $exercise
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function fetchExercise(Exercise $exercise): JsonResponse
    {
        $this->authorizeForUser($this->user(), 'access', $exercise);
        return $this->response($exercise);
    }

    /**
     * @param Lesson $lesson
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function fetchExercisesOfLesson(Lesson $lesson): JsonResponse
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);
        return $this->response($lesson->exercises);
    }

    /**
     * @param UpdateExerciseRequest $request
     * @param Exercise              $exercise
     * @return JsonResponse
     */
    public function updateExercise(UpdateExerciseRequest $request, Exercise $exercise): JsonResponse
    {
        $exercise->update($request->all());
        return $this->response($exercise);
    }

    /**
     * @param Exercise $exercise
     * @throws Exception
     */
    public function deleteExercise(Exercise $exercise)
    {
        $this->authorizeForUser($this->user(), 'modify', $exercise);
        $exercise->delete();
    }

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
