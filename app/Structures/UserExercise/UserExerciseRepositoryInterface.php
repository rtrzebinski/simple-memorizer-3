<?php

namespace App\Structures\UserExercise;

use Illuminate\Support\Collection;

/**
 * UserExercise operations valid for all (authenticated and guest) users
 *
 * Interface UserExerciseRepositoryInterface
 * @package App\Structures\UserExercise
 */
interface UserExerciseRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesOfLesson(int $lessonId): Collection;
}
