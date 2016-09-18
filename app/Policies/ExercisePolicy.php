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
     * Whether user is permitted to fetch exercise.
     * @param User $user
     * @param Exercise $exercise
     * @return bool
     */
    public function fetch(User $user, Exercise $exercise) : bool
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
     * Whether user is permitted to update exercise.
     * @param User $user
     * @param Exercise $exercise
     * @return bool
     */
    public function update(User $user, Exercise $exercise): bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->join('exercises', 'exercises.lesson_id', '=', 'lessons.id')
            ->where('exercises.id', '=', $exercise->id)
            ->where('users.id', '=', $user->id)
            ->exists();
    }

    /**
     * Whether user is permitted to delete exercise.
     * @param User $user
     * @param Exercise $exercise
     * @return bool
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        // if user can update exercise, he also can delete it
        return $this->update($user, $exercise);
    }

    /**
     * Whether user is permitted to answer exercise question.
     * @param User $user
     * @param Exercise $exercise
     * @return bool
     */
    public function answerQuestion(User $user, Exercise $exercise) : bool
    {
        // if user can fetch exercise, he also can answer question
        return $this->fetch($user, $exercise);
    }
}
