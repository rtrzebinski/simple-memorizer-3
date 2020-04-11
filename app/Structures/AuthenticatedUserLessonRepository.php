<?php

namespace App\Structures;

use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuthenticatedUserLessonRepository implements AuthenticatedUserLessonRepositoryInterface
{
    /**
     * @param User $user
     * @return Collection
     */
    public function fetchOwnedUserLessons(User $user): Collection
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
                DB::raw('CASE WHEN lu.id IS NOT NULL THEN 1 ELSE 0 END AS is_subscriber'),
                DB::raw('COALESCE(lu.bidirectional, 0) AS is_bidirectional'),
                DB::raw('COALESCE(lu.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->leftJoin('lesson_user AS lu', function (JoinClause $joinClause) use ($user) {
                $joinClause
                    ->on('lu.lesson_id', '=', 'l.id')
                    ->where('lu.user_id', '=', $user->id);
            })
            ->where('l.owner_id', $user->id)
            ->get()
            ->mapInto(UserLesson::class);
    }

    /**
     * @param User $user
     * @return Collection
     */
    public function fetchSubscribedUserLessons(User $user): Collection
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
                DB::raw('CASE WHEN lu.id IS NOT NULL THEN 1 ELSE 0 END AS is_subscriber'),
                DB::raw('COALESCE(lu.bidirectional, 0) AS is_bidirectional'),
                DB::raw('COALESCE(lu.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->join('lesson_user AS lu', function (JoinClause $joinClause) use ($user) {
                $joinClause
                    ->on('lu.lesson_id', '=', 'l.id')
                    ->where('lu.user_id', '=', $user->id);
            })
            ->where('l.owner_id', '!=', $user->id)
            ->get()
            ->mapInto(UserLesson::class);
    }

    /**
     * @param User $user
     * @return Collection
     */
    public function fetchAvailableUserLessons(User $user): Collection
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
                DB::raw('CASE WHEN lu.id IS NOT NULL THEN 1 ELSE 0 END AS is_subscriber'),
                DB::raw('COALESCE(lu.bidirectional, 0) AS is_bidirectional'),
                DB::raw('COALESCE(lu.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->leftJoin('lesson_user AS lu', function (JoinClause $joinClause) use ($user) {
                $joinClause
                    ->on('lu.lesson_id', '=', 'l.id')
                    ->where('lu.user_id', '=', $user->id);
            })
            ->whereNull('lu.id')
            ->where('l.visibility', '=', 'public')
            ->get()
            ->mapInto(UserLesson::class);
    }
}
