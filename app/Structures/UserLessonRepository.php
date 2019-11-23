<?php

namespace App\Structures;

use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class UserLessonRepository
{
    /**
     * @param User $user
     * @param int  $lessonId
     * @return UserLesson|null
     */
    public function fetchUserLesson(User $user, int $lessonId): ?UserLesson
    {
        return DB::table('lessons')
            ->select([
                DB::raw($user->id.' AS user_id'),
                'l.id AS lesson_id',
                'l.owner_id AS owner_id',
                'l.name AS name',
                'l.visibility AS visibility',
                'l.exercises_count AS exercises_count',
                'l.subscribers_count AS subscribers_count',
                'l.child_lessons_count AS child_lessons_count',
                DB::raw('COALESCE(lu.id, 0) AS is_subscriber'),
                DB::raw('COALESCE(lu.bidirectional, 0) AS is_bidirectional'),
                DB::raw('COALESCE(lu.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->leftJoin('lesson_user AS lu', function (JoinClause $joinClause) use ($user, $lessonId) {
                $joinClause
                    ->on('lu.lesson_id', '=', 'l.id')
                    ->where('lu.user_id', '=', $user->id)
                    ->where('lu.lesson_id', '=', $lessonId);
            })
            ->where('l.id', $lessonId)
            ->get()
            ->mapInto(UserLesson::class)
            ->first();
    }
}
