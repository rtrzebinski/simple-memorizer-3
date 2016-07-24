<?php

namespace App\Models\Exercise;

use Illuminate\Support\Collection;

class ExerciseRepository implements ExerciseRepositoryInterface
{
    /**
     * @param int $userId
     * @return Collection
     */
    public function fetchExercisesOfUser(int $userId) : Collection
    {
        return Exercise::whereUserId($userId)->get();
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
     * @param int $userId
     * @param array $attributes
     * @return Exercise
     */
    public function createExercise(int $userId, array $attributes) : Exercise
    {
        $exercise = new Exercise($attributes);
        $exercise->user_id = $userId;
        $exercise->save();
        return $exercise;
    }

    /**
     * @param int $exerciseId
     * @param array $attributes
     * @return Exercise
     */
    public function updateExercise(int $exerciseId, array $attributes) : Exercise
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
