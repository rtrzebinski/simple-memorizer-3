<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;

class LessonPolicy
{
    use HandlesAuthorization;

    /**
     * User must be the owner of lesson or lesson must be public.
     *
     * @param User   $user
     * @param Lesson $lesson
     * @return bool
     */
    public function access(User $user = null, Lesson $lesson): bool
    {
        if ($lesson->visibility == 'public') {
            return true;
        }

        if (!$user) {
            return false;
        }

        return Lesson::whereId($lesson->id)
            ->where(function (Builder $query) use ($user) {
                $query->where('visibility', '=', 'public')
                    ->orWhere('owner_id', '=', $user->id);
            })
            ->exists();
    }

    /**
     * User must be the owner of lesson.
     *
     * @param User   $user
     * @param Lesson $lesson
     * @return bool
     */
    public function modify(User $user, Lesson $lesson): bool
    {
        return Lesson::query()
            ->where('lessons.id', '=', $lesson->id)
            ->whereOwnerId($user->id)
            ->exists();
    }

    /**
     * User must not subscribe lesson and not be the owner.
     *
     * @param User   $user
     * @param Lesson $lesson
     * @return bool
     */
    public function subscribe(User $user, Lesson $lesson): bool
    {
        return Lesson::query()
            ->where('lessons.id', '=', $lesson->id)
            ->where('lessons.owner_id', '!=', $user->id)
            ->where('lessons.visibility', '=', 'public')
            ->whereNotIn('lessons.id', $user->subscribedLessons()->pluck('lessons.id'))
            ->exists();
    }

    /**
     * User must subscribe lesson and not be the owner.
     *
     * @param User   $user
     * @param Lesson $lesson
     * @return bool
     */
    public function unsubscribe(User $user, Lesson $lesson): bool
    {
        return Lesson::query()
            ->where('lessons.id', '=', $lesson->id)
            ->join('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('lesson_user.user_id', '=', $user->id)
            ->where('lessons.owner_id', '!=', $user->id)
            ->exists();
    }

    /**
     * Lesson must have certain number of exercises, and user must have access.
     *
     * @param User   $user
     * @param Lesson $lesson
     * @return bool
     */
    public function learn(User $user, Lesson $lesson)
    {
        return ($lesson->exercises_count >= config('app.min_exercises_to_learn_lesson')) &&
            $this->access($user, $lesson);
    }
}
