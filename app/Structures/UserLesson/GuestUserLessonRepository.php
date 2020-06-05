<?php

namespace App\Structures\UserLesson;

use App\Models\Lesson;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * UserLesson operations valid for guest users only
 *
 * Class GuestUserLessonRepository
 * @package App\Structures\UserLesson
 */
class GuestUserLessonRepository implements AbstractUserLessonRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return UserLesson|null
     */
    public function fetchUserLesson(int $lessonId): ?UserLesson
    {
        return DB::table('lessons')
            ->select([
                'l.id AS lesson_id',
                'l.owner_id AS owner_id',
                'l.name AS name',
                'l.visibility AS visibility',
                'l.exercises_count AS exercises_count',
                'l.subscribers_count AS subscribers_count',
                'l.child_lessons_count AS child_lessons_count',
            ])
            ->from('lessons AS l')
            ->where('l.id', $lessonId)
            ->get()
            ->mapInto(UserLesson::class)
            ->first();
    }

    /**
     * @return Collection|UserLesson[]
     */
    public function fetchAvailableUserLessons(): Collection
    {
        return DB::table('lessons')
            ->select([
                'l.id AS lesson_id',
                'l.owner_id AS owner_id',
                'l.name AS name',
                'l.visibility AS visibility',
                'l.exercises_count AS exercises_count',
                'l.subscribers_count AS subscribers_count',
                'l.child_lessons_count AS child_lessons_count',
            ])
            ->from('lessons AS l')
            ->where('l.visibility', '=', Lesson::VISIBILITY_PUBLIC)
            ->where('l.exercises_count', '>=', config('app.min_exercises_to_learn_lesson'))
            ->get()
            ->mapInto(UserLesson::class);
    }
}
