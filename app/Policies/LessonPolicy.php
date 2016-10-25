<?php

namespace App\Policies;

use App\Models\Lesson\Lesson;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;

class LessonPolicy
{
    use HandlesAuthorization;

    /**
     * User must be the owner of lesson, or must subscribe lesson.
     *
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function access(User $user, Lesson $lesson) : bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('lessons.id', '=', $lesson->id)
            ->where(function (Builder $query) use ($user) {
                $query->where('users.id', '=', $user->id)
                    ->orWhere('lesson_user.user_id', '=', $user->id);
            })
            ->exists();
    }

    /**
     * User must be the owner of lesson.
     *
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function modify(User $user, Lesson $lesson) : bool
    {
        return Lesson::whereId($lesson->id)
            ->whereOwnerId($user->id)
            ->exists();
    }

    /**
     * User must not subscribe lesson.
     *
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function subscribe(User $user, Lesson $lesson) : bool
    {
        return Lesson::whereId($lesson->id)
            ->where(function (Builder $query) use ($user) {
                $query->where('visibility', '=', 'public')
                    ->orWhere('owner_id', '=', $user->id);
            })
            ->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->whereNull('lesson_user.user_id')
            ->exists();
    }

    /**
     * User must subscribe lesson.
     *
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function unsubscribe(User $user, Lesson $lesson) : bool
    {
        return Lesson::whereId($lesson->id)
            ->join('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('lesson_user.user_id', '=', $user->id)
            ->exists();
    }
}
