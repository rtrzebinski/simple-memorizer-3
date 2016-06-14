<?php

namespace App\Repositories;

use App\Exercise;
use Illuminate\Support\Collection;

class ExerciseRepository
{
    public function fetchExercisesOfUser(int $userId) : Collection
    {
        return Exercise::whereUserId($userId)->get();
    }

    public function findExerciseById(int $exerciseId) : Exercise
    {
        return Exercise::findOrFail($exerciseId);
    }

    public function createExercise(int $userId, array $input) : Exercise
    {
        $exercise = new Exercise($input);
        $exercise->user_id = $userId;
        $exercise->save();
        return $exercise;
    }

    public function updateExercise(int $exerciseId, array $input) : Exercise
    {
        $exercise = $this->findExerciseById($exerciseId);
        $exercise->fill($input);
        $exercise->save();
        return $exercise;
    }

    public function deleteExercise(int $exerciseId)
    {
        $exercise = $this->findExerciseById($exerciseId);
        $exercise->delete();
    }
}
