<?php

namespace App\Policies;

use App\Models\Exercise\Exercise;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;

class ExercisePolicy
{
    use HandlesAuthorization;

    /**
     * User must be the owner of the lesson exercise belongs to or must subscribe lesson.
     *
     * @param User $user
     * @param Exercise $exercise
     * @return bool
     */
    public function access(User $user, Exercise $exercise) : bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->join('exercises', 'exercises.lesson_id', '=', 'lessons.id')
            ->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('exercises.id', '=', $exercise->id)
            ->where(function (Builder $query) use ($user) {
                $query->where('users.id', '=', $user->id)
                    ->orWhere('lesson_user.user_id', '=', $user->id);
            })
            ->exists();
    }

    /**
     * User must be the owner of lesson exercise belongs to.
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
