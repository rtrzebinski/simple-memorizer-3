<?php

namespace App\Models\Exercise;

use Illuminate\Support\Collection;

class ExerciseRepository implements ExerciseRepositoryInterface
{
    /**
     * @param array $attributes
     * @param int $lessonId
     * @return Exercise
     */
    public function createExercise(array $attributes, int $lessonId) : Exercise
    {
        $exercise = new Exercise($attributes);
        $exercise->lesson_id = $lessonId;
        $exercise->save();
        return $exercise;
    }

    /**
     * @param int $exerciseId
     * @return Exercise
     */
    public function findExerciseById(int $exerciseId) : Exercise
    {
        return Exercise::findOrFail($exerciseId);
    }

    /**
     * @param int $lessonId
     * @return Collection
     */
    public function fetchExercisesOfLesson(int $lessonId) : Collection
    {
        return Exercise::whereLessonId($lessonId)->get();
    }

    /**
     * @param array $attributes
     * @param int $exerciseId
     * @return Exercise
     */
    public function updateExercise(array $attributes, int $exerciseId) : Exercise
    {
        $exercise = Exercise::findOrFail($exerciseId);
        $exercise->fill($attributes);
        $exercise->save();
        return $exercise;
    }

    /**
     * @param int $exerciseId
     * @throws \Exception
     */
    public function deleteExercise(int $exerciseId)
    {
        Exercise::findOrFail($exerciseId)->delete();
    }
}
