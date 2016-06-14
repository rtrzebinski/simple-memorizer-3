<?php

namespace App\Repositories;

use App\Exercise;
use Illuminate\Support\Collection;

class ExerciseRepository
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
     * @param array $input
     * @return Exercise
     */
    public function createExercise(int $userId, array $input) : Exercise
    {
        $exercise = new Exercise($input);
        $exercise->user_id = $userId;
        $exercise->save();
        return $exercise;
    }

    /**
     * @param int $exerciseId
     * @param array $input
     * @return Exercise
     */
    public function updateExercise(int $exerciseId, array $input) : Exercise
    {
        $exercise = $this->findExerciseById($exerciseId);
        $exercise->fill($input);
        $exercise->save();
        return $exercise;
    }

    /**
     * @param int $exerciseId
     * @throws \Exception
     */
    public function deleteExercise(int $exerciseId)
    {
        $exercise = $this->findExerciseById($exerciseId);
        $exercise->delete();
    }
}
