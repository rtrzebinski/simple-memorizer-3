<?php

namespace App\Models\Exercise;

use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExerciseRepository implements ExerciseRepositoryInterface
{
    /**
     * @param array $attributes
     * @param int $lessonId
     * @return Exercise
     */
    public function createExercise(array $attributes, int $lessonId) : Exercise
    {
        $exercise = new Exercise($attributes);
        $exercise->lesson_id = $lessonId;
        $exercise->save();
        return $exercise;
    }

    /**
     * @param int $exerciseId
     * @return Exercise
     */
    public function findExerciseById(int $exerciseId) : Exercise
    {
        return Exercise::findOrFail($exerciseId);
    }

    /**
     * @param int $lessonId
     * @return Collection
     */
    public function fetchExercisesOfLesson(int $lessonId) : Collection
    {
        return Exercise::whereLessonId($lessonId)->get();
    }

    /**
     * @param array $attributes
     * @param int $exerciseId
     * @return Exercise
     */
    public function updateExercise(array $attributes, int $exerciseId) : Exercise
    {
        $exercise = Exercise::findOrFail($exerciseId);
        $exercise->fill($attributes);
        $exercise->save();
        return $exercise;
    }

    /**
     * @param int $exerciseId
     * @throws Exception
     */
    public function deleteExercise(int $exerciseId)
    {
        Exercise::findOrFail($exerciseId)->delete();
    }

    /**
     * Whether user is permitted to create exercise of lesson.
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function authorizeCreateExercise(int $userId, int $lessonId) : bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->where('lessons.id', '=', $lessonId)
            ->where('users.id', '=', $userId)
            ->exists();
    }

    /**
     * Whether user is permitted to fetch exercise by id.
     * @param int $userId
     * @param int $exerciseId
     * @return bool
     */
    public function authorizeFetchExerciseById(int $userId, int $exerciseId) : bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->join('exercises', 'exercises.lesson_id', '=', 'lessons.id')
            ->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('exercises.id', '=', $exerciseId)
            ->where(function (Builder $query) use ($userId) {
                $query->where('users.id', '=', $userId)
                    ->orWhere('lesson_user.user_id', '=', $userId);
            })
            ->exists();
    }

    /**
     * Whether user is permitted to fetch exercises of lesson.
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function authorizeFetchExercisesOfLesson(int $userId, int $lessonId) : bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('lessons.id', '=', $lessonId)
            ->where(function (Builder $query) use ($userId) {
                $query->where('users.id', '=', $userId)
                    ->orWhere('lesson_user.user_id', '=', $userId);
            })
            ->exists();
    }

    /**
     * Whether user is permitted to update exercise.
     * @param int $userId
     * @param int $exerciseId
     * @return bool
     */
    public function authorizeUpdateExercise(int $userId, int $exerciseId): bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->join('exercises', 'exercises.lesson_id', '=', 'lessons.id')
            ->where('exercises.id', '=', $exerciseId)
            ->where('users.id', '=', $userId)
            ->exists();
    }

    /**
     * Whether user is permitted to delete exercise.
     * @param int $userId
     * @param int $exerciseId
     * @return bool
     */
    public function authorizeDeleteExercise(int $userId, int $exerciseId): bool
    {
        return User::query()
            ->join('lessons', 'lessons.owner_id', '=', 'users.id')
            ->join('exercises', 'exercises.lesson_id', '=', 'lessons.id')
            ->where('exercises.id', '=', $exerciseId)
            ->where('users.id', '=', $userId)
            ->exists();
    }
}
