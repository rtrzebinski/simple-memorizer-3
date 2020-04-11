<?php

namespace App\Structures;

use App\Models\User;
use Illuminate\Support\Collection;

interface AuthenticatedUserExerciseRepositoryInterface
{
    /**
     * @param User $user
     * @param int  $exerciseId
     * @return UserExercise
     * @throws \Exception
     */
    public function fetchUserExerciseOfExercise(User $user, int $exerciseId): UserExercise;

    /**
     * @param User   $user
     * @param string $phrase
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesWithPhrase(User $user, string $phrase): Collection;
}
