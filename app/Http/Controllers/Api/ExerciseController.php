<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AccessExerciseRequest;
use App\Http\Requests\Api\CreateExerciseRequest;
use App\Http\Requests\Api\UpdateExerciseRequest;
use App\Http\Requests\Api\DeleteExerciseRequest;
use App\Repositories\ExerciseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class ExerciseController
 * @package App\Http\Controllers\Api
 */
class ExerciseController extends Controller
{
    /**
     * @param ExerciseRepository $exerciseRepository
     * @param CreateExerciseRequest $request
     * @return JsonResponse
     */
    public function createExercise(ExerciseRepository $exerciseRepository, CreateExerciseRequest $request)
    {
        $exercise = $exerciseRepository->createExercise($this->user()->id, $request->all());
        return $this->response($exercise, Response::HTTP_CREATED);
    }

    /**
     * @param ExerciseRepository $exerciseRepository
     * @return JsonResponse
     */
    public function fetchExercisesOfUser(ExerciseRepository $exerciseRepository)
    {
        $exercises = $exerciseRepository->fetchExercisesOfUser($this->user()->id);
        return $this->response($exercises);
    }

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param AccessExerciseRequest $request
     * @return JsonResponse
     */
    public function fetchExercise(ExerciseRepository $exerciseRepository, AccessExerciseRequest $request)
    {
        $exercise = $exerciseRepository->findExerciseById($request->exercise_id);
        return $this->response($exercise);
    }

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param UpdateExerciseRequest $request
     * @return JsonResponse
     */
    public function updateExercise(ExerciseRepository $exerciseRepository, UpdateExerciseRequest $request)
    {
        $exercise = $exerciseRepository->updateExercise($request->exercise_id, $request->except('exercise_id'));
        return $this->response($exercise);
    }

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param DeleteExerciseRequest $request
     * @return Response
     */
    public function deleteExercise(ExerciseRepository $exerciseRepository, DeleteExerciseRequest $request)
    {
        $exerciseRepository->deleteExercise($request->exercise_id);
        return $this->status(Response::HTTP_NO_CONTENT);
    }
}
