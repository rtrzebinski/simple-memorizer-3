<?php

namespace App\Models\Lesson;

use App\Models\User\User;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LessonRepository implements LessonRepositoryInterface
{
    /**
     * @param array $attributes
     * @param int $userId
     * @return Lesson
     */
    public function createLesson(array $attributes, int $userId) : Lesson
    {
        $lesson = new Lesson($attributes);
        $lesson->owner_id = $userId;
        $lesson->save();
        return $lesson;
    }

    /**
     * @param int $userId
     * @param int $lessonId
     */
    public function subscribeLesson(int $userId, int $lessonId)
    {
        DB::table('lesson_user')->insert([
            'user_id' => $userId,
            'lesson_id' => $lessonId,
        ]);
    }

    /**
     * @param int $userId
     * @param int $lessonId
     */
    public function unsubscribeLesson(int $userId, int $lessonId)
    {
        DB::table('lesson_user')
            ->where('user_id', '=', $userId)
            ->where('lesson_id', '=', $lessonId)
            ->delete();
    }

    /**
     * @param array $attributes
     * @param int $lessonId
     * @return Lesson
     */
    public function updateLesson(array $attributes, int $lessonId) : Lesson
    {
        $lesson = Lesson::findOrFail($lessonId);
        $lesson->update($attributes);
        return $lesson;
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function fetchOwnedLessons(int $userId) : Collection
    {
        /** @var User $user */
        $user = User::findOrFail($userId);
        return $user->ownedLessons;
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function fetchSubscribedLessons(int $userId) : Collection
    {
        /** @var User $user */
        $user = User::findOrFail($userId);
        return $user->subscribedLessons;
    }

    /**
     * @param int $lessonId
     * @throws Exception
     */
    public function deleteLesson(int $lessonId)
    {
        Lesson::findOrFail($lessonId)->delete();
    }

    /**
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function authorizeSubscribeLesson(int $userId, int $lessonId) : bool
    {
        return Lesson::whereId($lessonId)
            ->where(function (Builder $query) use ($userId) {
                $query->where('visibility', '=', 'public')
                    ->orWhere('owner_id', '=', $userId);
            })
            ->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->whereNull('lesson_user.user_id')
            ->exists();
    }

    /**
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function authorizeUnsubscribeLesson(int $userId, int $lessonId) : bool
    {
        return Lesson::whereId($lessonId)
            ->join('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where('lesson_user.user_id', '=', $userId)
            ->exists();
    }

    /**
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function authorizeUpdateLesson(int $userId, int $lessonId) : bool
    {
        return Lesson::whereId($lessonId)
            ->whereOwnerId($userId)
            ->exists();
    }

    /**
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function authorizeDeleteLesson(int $userId, int $lessonId) : bool
    {
        return Lesson::whereId($lessonId)
            ->whereOwnerId($userId)
            ->exists();
    }
}
