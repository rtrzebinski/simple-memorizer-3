<?php

namespace App\Structures;

use Illuminate\Support\Collection;

interface UserExerciseRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesOfLesson(int $lessonId): Collection;
}
