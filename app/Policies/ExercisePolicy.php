<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\Lesson;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;

class ExercisePolicy
{
    use HandlesAuthorization;

    /**
     * User must subscribe lesson in order to access exercise.
     *
     * @param User $user
     * @param Exercise $exercise
     * @return bool
     */
    public function access(User $user, Exercise $exercise): bool
    {
        return Lesson::query()
            ->where('lessons.id', '=', $exercise->lesson_id)
            ->join('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('lesson_user.user_id', '=', $user->id)
            ->exists();
    }

    /**
     * User must be the owner of lesson exercise belongs to in order to modify exercise.
     *
     * @param User $user
     * @param Exercise $exercise
     * @return bool
     */
    public function modify(User $user, Exercise $exercise): bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->join('exercises', 'exercises.lesson_id', '=', 'lessons.id')
            ->where('exercises.id', '=', $exercise->id)
            ->where('users.id', '=', $user->id)
            ->exists();
    }
}
