<?php

namespace App\Structures\UserLesson;

use App\Models\Lesson;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * UserLesson operations valid for authenticated users only
 *
 * Class AuthenticatedUserLessonRepository
 * @package App\Structures\UserLesson
 */
class AuthenticatedUserLessonRepository implements AuthenticatedUserLessonRepositoryInterface
{
    /**
     * @var Authenticatable
     */
    private Authenticatable $user;

    /**
     * AuthenticatedUserLessonRepository constructor.
     * @param Authenticatable $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * @param int $lessonId
     * @return UserLesson|null
     */
    public function fetchUserLesson(int $lessonId): ?UserLesson
    {
        return DB::table('lessons')
            ->select([
                DB::raw($this->user->id.' AS user_id'),
                'l.id AS lesson_id',
                'l.owner_id AS owner_id',
                'l.name AS name',
                'l.visibility AS visibility',
                'l.exercises_count AS exercises_count',
                'l.subscribers_count AS subscribers_count',
                'l.child_lessons_count AS child_lessons_count',
                DB::raw('CASE WHEN lu.id IS NOT NULL THEN 1 ELSE 0 END AS is_subscriber'),
                DB::raw('COALESCE(lu.bidirectional, 0) AS is_bidirectional'),
                DB::raw('COALESCE(lu.favourite, 0) AS is_favourite'),
                DB::raw('COALESCE(lu.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->leftJoin('lesson_user AS lu', function (JoinClause $joinClause) use ($lessonId) {
                $joinClause
                    ->on('lu.lesson_id', '=', 'l.id')
                    ->where('lu.user_id', '=', $this->user->id)
                    ->where('lu.lesson_id', '=', $lessonId);
            })
            ->where('l.id', $lessonId)
            ->get()
            ->mapInto(UserLesson::class)
            ->first();
    }

    /**
     * @return Collection|UserLesson[]
     */
    public function fetchOwnedUserLessons(): Collection
    {
        return DB::table('lessons')
            ->select([
                DB::raw($this->user->id.' AS user_id'),
                'l.id AS lesson_id',
                'l.owner_id AS owner_id',
                'l.name AS name',
                'l.visibility AS visibility',
                'l.exercises_count AS exercises_count',
                'l.subscribers_count AS subscribers_count',
                'l.child_lessons_count AS child_lessons_count',
                DB::raw('CASE WHEN lu.id IS NOT NULL THEN 1 ELSE 0 END AS is_subscriber'),
                DB::raw('COALESCE(lu.bidirectional, 0) AS is_bidirectional'),
                DB::raw('COALESCE(lu.favourite, 0) AS is_favourite'),
                DB::raw('COALESCE(lu.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->leftJoin('lesson_user AS lu', function (JoinClause $joinClause) {
                $joinClause
                    ->on('lu.lesson_id', '=', 'l.id')
                    ->where('lu.user_id', '=', $this->user->id);
            })
            ->where('l.owner_id', $this->user->id)
            ->get()
            ->mapInto(UserLesson::class);
    }

    /**
     * @return Collection|UserLesson[]
     */
    public function fetchSubscribedUserLessons(): Collection
    {
        return DB::table('lessons')
            ->select([
                DB::raw($this->user->id.' AS user_id'),
                'l.id AS lesson_id',
                'l.owner_id AS owner_id',
                'l.name AS name',
                'l.visibility AS visibility',
                'l.exercises_count AS exercises_count',
                'l.subscribers_count AS subscribers_count',
                'l.child_lessons_count AS child_lessons_count',
                DB::raw('CASE WHEN lu.id IS NOT NULL THEN 1 ELSE 0 END AS is_subscriber'),
                DB::raw('COALESCE(lu.bidirectional, 0) AS is_bidirectional'),
                DB::raw('COALESCE(lu.favourite, 0) AS is_favourite'),
                DB::raw('COALESCE(lu.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->from('lessons AS l')
            ->join('lesson_user AS lu', function (JoinClause $joinClause) {
                $joinClause
                    ->on('lu.lesson_id', '=', 'l.id')
                    ->where('lu.user_id', '=', $this->user->id);
            })
            ->where('l.owner_id', '!=', $this->user->id)
            ->get()
            ->mapInto(UserLesson::class);
    }

    /**
     * @return Collection|UserLesson[]
     */
    public function fetchAvailableUserLessons(): Collection
    {
        return DB::table('lessons')
            ->select([
                DB::raw($this->user->id.' AS user_id'),
                'l.id AS lesson_id',
                'l.owner_id AS owner_id',
                'l.name AS name',
                'l.visibility AS visibility',
                'l.exercises_count AS exercises_count',
                'l.subscribers_count AS subscribers_count',
                'l.child_lessons_count AS child_lessons_count',
            ])
            ->from('lessons AS l')
            ->leftJoin('lesson_user AS lu', function (JoinClause $joinClause) {
                $joinClause
                    ->on('lu.lesson_id', '=', 'l.id')
                    ->where('lu.user_id', '=', $this->user->id);
            })
            ->whereNull('lu.id')
            ->where('l.visibility', '=', Lesson::VISIBILITY_PUBLIC)
            ->where('l.exercises_count', '>=', config('app.min_exercises_to_learn_lesson'))
            ->get()
            ->mapInto(UserLesson::class);
    }
}
