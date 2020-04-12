<?php

namespace App\Structures\UserExercise;

use Illuminate\Support\Collection;

interface AuthenticatedUserExerciseRepositoryInterface extends AbstractUserExerciseRepositoryInterface
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
