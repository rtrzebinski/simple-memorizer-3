<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ExerciseAccessRequest;
use App\Http\Requests\Api\ExerciseCreateRequest;
use App\Http\Requests\Api\ExerciseUpdateRequest;
use App\Http\Requests\Api\ExerciseDeleteRequest;
use App\Repositories\ExerciseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExerciseController extends Controller
{
    /**
     * @param ExerciseRepository $exerciseRepository
     * @param ExerciseCreateRequest $request
     * @return JsonResponse
     */
    public function createExercise(ExerciseRepository $exerciseRepository, ExerciseCreateRequest $request)
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
     * @param ExerciseAccessRequest $request
     * @return JsonResponse
     */
    public function fetchExercise(ExerciseRepository $exerciseRepository, ExerciseAccessRequest $request)
    {
        $exercise = $exerciseRepository->findExerciseById($request->exercise_id);
        return $this->response($exercise);
    }

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param ExerciseUpdateRequest $request
     * @return JsonResponse
     */
    public function updateExercise(ExerciseRepository $exerciseRepository, ExerciseUpdateRequest $request)
    {
        $exercise = $exerciseRepository->updateExercise($request->exercise_id, $request->except('exercise_id'));
        return $this->response($exercise);
    }

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param ExerciseDeleteRequest $request
     * @return Response
     */
    public function deleteExercise(ExerciseRepository $exerciseRepository, ExerciseDeleteRequest $request)
    {
        $exerciseRepository->deleteExercise($request->exercise_id);
        return $this->status(Response::HTTP_NO_CONTENT);
    }
}
