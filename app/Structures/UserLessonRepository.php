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
     * @return UserLesson
     */
    public function fetchUserLesson(User $user, int $lessonId): UserLesson
    {
        return DB::table('lessons')
            ->select([
                'l.id AS lesson_id',
                'l.owner_id AS owner_id',
                'l.name AS name',
                DB::raw('COALESCE(lu.bidirectional, 0) AS is_bidirectional'),
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
