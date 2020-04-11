<?php

namespace App\Structures;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GuestUserLessonRepository implements GuestUserLessonRepositoryInterface
{
    /**
     * @return Collection
     */
    public function fetchPublicUserLessons(): Collection
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
            ->get()
            ->mapInto(UserLesson::class);
    }
}
