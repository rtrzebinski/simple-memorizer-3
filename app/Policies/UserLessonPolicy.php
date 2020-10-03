<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Structures\UserLesson\UserLesson;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;

class UserLessonPolicy
{
    use HandlesAuthorization;

    /**
     * Lesson must be public or user must be lesson owner.
     *
     * @param User|null $user
     * @param UserLesson $userLesson
     * @return bool
     */
    public function access(?User $user, UserLesson $userLesson): bool
    {
        return $userLesson->visibility == Lesson::VISIBILITY_PUBLIC || $user->id == $userLesson->owner_id;
    }

    /**
     * Lesson must have certain number of exercises, and user must have access.
     *
     * @param User $user
     * @param UserLesson $userLesson
     * @return bool
     */
    public function learn(User $user, UserLesson $userLesson): bool
    {
        return ($userLesson->exercises_count >= config('app.min_exercises_to_learn_lesson'))
            && $this->access($user, $userLesson);
    }

    /**
     * Owner can modify.
     *
     * @param User $user
     * @param UserLesson $userLesson
     * @return bool
     */
    public function modify(User $user, UserLesson $userLesson): bool
    {
        return $user->id == $userLesson->owner_id;
    }
}
