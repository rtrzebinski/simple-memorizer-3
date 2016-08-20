<?php

namespace App\Models\Exercise;

use Illuminate\Support\Collection;

interface ExerciseRepositoryInterface
{
    public function createExercise(array $attributes, int $lessonId) : Exercise;

    public function findExerciseById(int $exerciseId) : Exercise;

    public function fetchExercisesOfLesson(int $lessonId) : Collection;

    public function updateExercise(array $attributes, int $exerciseId) : Exercise;

    public function deleteExercise(int $exerciseId);

    public function authorizeCreateExercise(int $userId, int $lessonId) : bool;

    public function authorizeFetchExerciseById(int $userId, int $exerciseId) : bool;

    public function authorizeFetchExercisesOfLesson(int $userId, int $lessonId) : bool;

    public function authorizeUpdateExercise(int $userId, int $exerciseId): bool;

    public function authorizeDeleteExercise(int $userId, int $exerciseId): bool;
}
