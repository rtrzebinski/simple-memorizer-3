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
     * Whether user is permitted to subscribe lesson.
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
     * Whether user is permitted to unsubscribe lesson.
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

    /**
     *  Whether user is permitted to update lesson.
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function update(User $user, Lesson $lesson) : bool
    {
        return Lesson::whereId($lesson->id)
            ->whereOwnerId($user->id)
            ->exists();
    }

    /**
     * Whether user is permitted to delete lesson.
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function delete(User $user, Lesson $lesson) : bool
    {
        return Lesson::whereId($lesson->id)
            ->whereOwnerId($user->id)
            ->exists();
    }

    /**
     * Whether user is permitted to create exercise of lesson.
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function createExercise(User $user, Lesson $lesson) : bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->where('lessons.id', '=', $lesson->id)
            ->where('users.id', '=', $user->id)
            ->exists();
    }

    /**
     * Whether user is permitted to fetch exercises of lesson.
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function fetchExercisesOfLesson(User $user, Lesson $lesson) : bool
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
}
