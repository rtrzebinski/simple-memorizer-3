<?php

namespace App\Structures\UserExercise;

use Illuminate\Support\Collection;

interface AbstractUserExerciseRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesOfLesson(int $lessonId): Collection;
}
