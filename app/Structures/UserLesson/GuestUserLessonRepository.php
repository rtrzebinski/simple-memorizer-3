<?php

namespace App\Structures\UserLesson;

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
                DB::raw('0 AS is_subscriber'),
                DB::raw('0 AS is_bidirectional'),
                DB::raw('0 AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->where('l.id', $lessonId)
            ->get()
            ->mapInto(UserLesson::class)
            ->first();
    }

    /**
     * @return Collection
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
                DB::raw('0 AS is_subscriber'),
                DB::raw('0 AS is_bidirectional'),
                DB::raw('0 AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->where('l.visibility', '=', 'public')
            ->where('l.exercises_count', '>=', config('app.min_exercises_to_learn_lesson'))
            ->get()
            ->mapInto(UserLesson::class);
    }
}
