<?php

namespace App\Structures;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserExerciseRepositoryInterface
{
    /**
     * @param User|null $user
     * @param int       $lessonId
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesOfLesson(?User $user, int $lessonId): Collection;
}
