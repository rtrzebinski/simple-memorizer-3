<?php

namespace App\Structures;

use Illuminate\Support\Collection;

interface AuthenticatedUserExerciseRepositoryInterface
{
    /**
     * @param int  $exerciseId
     * @return UserExercise
     * @throws \Exception
     */
    public function fetchUserExerciseOfExercise(int $exerciseId): UserExercise;

    /**
     * @param string $phrase
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesWithPhrase(string $phrase): Collection;
}
