<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\FetchExerciseRequest;
use App\Http\Requests\CreateExerciseRequest;
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
        $exercise = $exerciseRepository->createExercise($this->user()->id, $request->all());
        return $this->response($exercise, Response::HTTP_CREATED);
    }

    /**
     * @param ExerciseRepositoryInterface $exerciseRepository
     * @return JsonResponse
     */
    public function fetchExercisesOfUser(ExerciseRepositoryInterface $exerciseRepository)
    {
        $exercises = $exerciseRepository->fetchExercisesOfUser($this->user()->id);
        return $this->response($exercises);
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
     * @param UpdateExerciseRequest $request
     * @return JsonResponse
     */
    public function updateExercise(ExerciseRepositoryInterface $exerciseRepository, UpdateExerciseRequest $request)
    {
        $exercise = $exerciseRepository->updateExercise($request->exercise_id, $request->except('exercise_id'));
        return $this->response($exercise);
    }

    /**
     * @param ExerciseRepositoryInterface $exerciseRepository
     * @param DeleteExerciseRequest $request
     * @return Response
     */
    public function deleteExercise(ExerciseRepositoryInterface $exerciseRepository, DeleteExerciseRequest $request)
    {
        $exerciseRepository->deleteExercise($request->exercise_id);
    }
}
