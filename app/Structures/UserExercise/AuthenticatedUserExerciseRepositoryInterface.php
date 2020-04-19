<?php

namespace App\Structures\UserExercise;

use Illuminate\Support\Collection;

/**
 * UserExercise operations valid for authenticated users only
 *
 * Interface AuthenticatedUserExerciseRepositoryInterface
 * @package App\Structures\UserExercise
 */
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
