<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Structures\UserExercise\UserExercise;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;

class UserExercisePolicy
{
    use HandlesAuthorization;

    /**
     * User must subscribe lesson to access user exercise;
     *
     * @param User $user
     * @param UserExercise $userExercise
     * @return bool
     */
    public function access(User $user, UserExercise $userExercise): bool
    {
        return Lesson::query()
            ->where('lessons.id', '=', $userExercise->lesson_id)
            ->join('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('lesson_user.user_id', '=', $user->id)
            ->exists();
    }
}
