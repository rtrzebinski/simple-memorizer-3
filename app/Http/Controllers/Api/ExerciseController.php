<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\FetchExerciseRequest;
use App\Http\Requests\CreateExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Http\Requests\DeleteExerciseRequest;
use App\Models\Exercise\ExerciseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
     * @param FetchExerciseRequest $request
     * @return JsonResponse
     */
    public function fetchExercise(ExerciseRepository $exerciseRepository, FetchExerciseRequest $request)
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
    }
}
