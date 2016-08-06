<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\FetchExerciseRequest;
use App\Http\Requests\CreateExerciseRequest;
use App\Http\Requests\FetchExercisesOfLessonRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Http\Requests\DeleteExerciseRequest;
use App\Models\Exercise\ExerciseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExerciseController extends Controller
{
    /**
     * @param ExerciseRepositoryInterface $exerciseRepository
     * @param CreateExerciseRequest $request
     * @return JsonResponse
     */
    public function createExercise(ExerciseRepositoryInterface $exerciseRepository, CreateExerciseRequest $request)
    {
        $exercise = $exerciseRepository->createExercise($request->except('lesson_id'), $request->lesson_id);
        return $this->response($exercise, Response::HTTP_CREATED);
    }

    /**
     * @param ExerciseRepositoryInterface $exerciseRepository
     * @param FetchExerciseRequest $request
     * @return JsonResponse
     */
    public function fetchExercise(ExerciseRepositoryInterface $exerciseRepository, FetchExerciseRequest $request)
    {
        $exercise = $exerciseRepository->findExerciseById($request->exercise_id);
        return $this->response($exercise);
    }

    /**
     * @param ExerciseRepositoryInterface $exerciseRepository
     * @param FetchExercisesOfLessonRequest $request
     * @return JsonResponse
     */
    public function fetchExercisesOfLesson(
        ExerciseRepositoryInterface $exerciseRepository,
        FetchExercisesOfLessonRequest $request
    ) {
        $exercises = $exerciseRepository->fetchExercisesOfLesson($request->lesson_id);
        return $this->response($exercises);
    }

    /**
     * @param ExerciseRepositoryInterface $exerciseRepository
     * @param UpdateExerciseRequest $request
     * @return JsonResponse
     */
    public function updateExercise(ExerciseRepositoryInterface $exerciseRepository, UpdateExerciseRequest $request)
    {
        $exercise = $exerciseRepository->updateExercise($request->except('exercise_id'), $request->exercise_id);
        return $this->response($exercise);
    }

    /**
     * @param ExerciseRepositoryInterface $exerciseRepository
     * @param DeleteExerciseRequest $request
     */
    public function deleteExercise(ExerciseRepositoryInterface $exerciseRepository, DeleteExerciseRequest $request)
    {
        $exerciseRepository->deleteExercise($request->exercise_id);
    }
}
