<?php

namespace App\Structures\UserExercise;

use Illuminate\Support\Collection;

/**
 * UserExercise operations valid for all users
 *
 * Interface UserExerciseRepositoryInterface
 * @package App\Structures\UserExercise
 */
interface AbstractUserExerciseRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesOfLesson(int $lessonId): Collection;
}
