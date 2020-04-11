<?php

namespace App\Http\Controllers\Api;

use App\Events\ExerciseCreated;
use App\Events\ExerciseDeleted;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\Lesson;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
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

        event(new ExerciseCreated($lesson, $this->user()));

        return $this->response($exercise);
    }

    /**
     * @param Exercise $exercise
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function fetchExercise(Exercise $exercise): JsonResponse
    {
        $this->authorizeForUser($this->user(), 'access', $exercise);
        return $this->response($exercise);
    }

    /**
     * @param Lesson $lesson
     * @return JsonResponse
     * @throws AuthorizationException
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

        event(new ExerciseDeleted($exercise->lesson, $this->user()));
    }
}
