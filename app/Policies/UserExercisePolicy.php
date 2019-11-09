<?php

namespace App\Policies;

use App\Structures\UserExercise;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserExercisePolicy
{
    use HandlesAuthorization;

    /**
     * User must be the owner of the lesson exercise belongs to or must subscribe lesson.
     *
     * @param User         $user
     * @param UserExercise $userExercise
     * @return bool
     */
    public function access(User $user, UserExercise $userExercise): bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->join('exercises', 'exercises.lesson_id', '=', 'lessons.id')
            ->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('exercises.id', '=', $userExercise->exercise_id)
            ->where(function (Builder $query) use ($user) {
                $query->where('users.id', '=', $user->id)
                    ->orWhere('lesson_user.user_id', '=', $user->id);
            })
            ->exists();
    }
}
