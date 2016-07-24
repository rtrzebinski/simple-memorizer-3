<?php

namespace App\Models\Exercise;

use Illuminate\Support\Collection;

interface ExerciseRepositoryInterface
{
    public function fetchExercisesOfUser(int $userId) : Collection;

    public function findExerciseById(int $exerciseId) : Exercise;

    public function createExercise(int $userId, array $attributes) : Exercise;

    public function updateExercise(int $exerciseId, array $attributes) : Exercise;

    public function deleteExercise(int $exerciseId);
}
