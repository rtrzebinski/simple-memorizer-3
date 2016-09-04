<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise\Exercise;
use App\Models\Lesson\Lesson;
use Exception;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    /**
     * @param CreateExerciseRequest $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function createExercise(CreateExerciseRequest $request, Lesson $lesson)
    {
        $exercise = new Exercise($request->all());
        $exercise->lesson_id = $lesson->id;
        $exercise->save();

        return $this->response($exercise);
    }

    /**
     * @param Exercise $exercise
     * @return JsonResponse
     */
    public function fetchExercise(Exercise $exercise)
    {
        $this->authorizeForUser($this->user(), 'fetch', $exercise);
        return $this->response($exercise);
    }

    /**
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function fetchExercisesOfLesson(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'fetchExercisesOfLesson', $lesson);
        return $this->response($lesson->exercises);
    }

    /**
     * @param UpdateExerciseRequest $request
     * @param Exercise $exercise
     * @return JsonResponse
     */
    public function updateExercise(UpdateExerciseRequest $request, Exercise $exercise)
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
        $this->authorizeForUser($this->user(), 'delete', $exercise);
        $exercise->delete();
    }
}
