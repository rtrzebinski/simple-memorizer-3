<?php

namespace App\Structures;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserExerciseRepository
{
    /**
     * @param User $user
     * @param int  $lessonId
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesOfLesson(User $user, int $lessonId): Collection
    {
        $lessons = DB::table('lessons AS l')
            ->select([
                'l_child.id AS child_id',
                'l.id AS lesson_id',
            ])
            ->leftJoin('lesson_aggregate AS la', 'la.parent_lesson_id', '=', 'l.id')
            ->leftJoin('lessons AS l_child', 'la.child_lesson_id', '=', 'l_child.id')
            ->where('l.id', $lessonId);

        return DB::table('exercises AS e')
            ->select([
                'e.id AS exercise_id',
                'e.lesson_id AS lesson_id',
                'e.question AS question',
                'e.answer AS answer',
                DB::raw('COALESCE(er.number_of_good_answers, 0) AS number_of_good_answers'),
                DB::raw('COALESCE(er.number_of_good_answers_today, 0) AS number_of_good_answers_today'),
                'er.latest_good_answer AS latest_good_answer',
                DB::raw('COALESCE(er.number_of_bad_answers, 0) AS number_of_bad_answers'),
                DB::raw('COALESCE(er.number_of_bad_answers_today, 0) AS number_of_bad_answers_today'),
                'er.latest_bad_answer AS latest_bad_answer',
                DB::raw('COALESCE(er.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->leftJoin('exercise_results AS er', function (JoinClause $joinClause) use ($user) {
                $joinClause
                    ->on('er.exercise_id', '=', 'e.id')
                    ->where('er.user_id', '=', $user->id);
            })
            ->joinSub($lessons, 'lessons', function (JoinClause $join) {
                $join
                    ->on('e.lesson_id', '=', 'lessons.child_id')
                    ->orOn('e.lesson_id', '=', 'lessons.lesson_id');
            })
            ->get()
            ->mapInto(UserExercise::class);
    }

    /**
     * @param User $user
     * @param int  $exerciseId
     * @return UserExercise
     * @throws \Exception
     */
    public function fetchUserExerciseOfExercise(User $user, int $exerciseId): UserExercise
    {
        $result = DB::table('exercises AS e')
            ->select([
                'e.id AS exercise_id',
                'e.lesson_id AS lesson_id',
                'e.question AS question',
                'e.answer AS answer',
                DB::raw('COALESCE(er.number_of_good_answers, 0) AS number_of_good_answers'),
                DB::raw('COALESCE(er.number_of_good_answers_today, 0) AS number_of_good_answers_today'),
                'er.latest_good_answer AS latest_good_answer',
                DB::raw('COALESCE(er.number_of_bad_answers, 0) AS number_of_bad_answers'),
                DB::raw('COALESCE(er.number_of_bad_answers_today, 0) AS number_of_bad_answers_today'),
                'er.latest_bad_answer AS latest_bad_answer',
                DB::raw('COALESCE(er.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->leftJoin('exercise_results AS er', function (JoinClause $joinClause) use ($user) {
                $joinClause
                    ->on('er.exercise_id', '=', 'e.id')
                    ->where('er.user_id', '=', $user->id);
            })
            ->where('e.id', '=', $exerciseId)
            ->first();

        if (!$result) {
            throw new \Exception('Exercise does not exist: '.$exerciseId);
        }

        return new UserExercise($result);
    }

    /**
     * @param User   $user
     * @param string $phrase
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesWithPhrase(User $user, string $phrase): Collection
    {
        return DB::table('exercises AS e')
            ->select([
                'e.id AS exercise_id',
                'e.lesson_id AS lesson_id',
                'l.name AS lesson_name',
                'e.question AS question',
                'e.answer AS answer',
                DB::raw('COALESCE(er.number_of_good_answers, 0) AS number_of_good_answers'),
                DB::raw('COALESCE(er.number_of_good_answers_today, 0) AS number_of_good_answers_today'),
                'er.latest_good_answer AS latest_good_answer',
                DB::raw('COALESCE(er.number_of_bad_answers, 0) AS number_of_bad_answers'),
                DB::raw('COALESCE(er.number_of_bad_answers_today, 0) AS number_of_bad_answers_today'),
                'er.latest_bad_answer AS latest_bad_answer',
                DB::raw('COALESCE(er.percent_of_good_answers, 0) AS percent_of_good_answers'),
            ])
            ->join('lessons AS l', function (JoinClause $join) use ($user) {
                $join->on('l.id', '=', 'e.lesson_id')
                    ->where('l.owner_id', '=', $user->id);
            })
            ->where(function (Builder $builder) use ($phrase) {
                $builder->where('question', 'like', '%'.$phrase.'%')
                    ->orWhere('answer', 'like', '%'.$phrase.'%');
            })
            ->leftJoin('exercise_results AS er', function (JoinClause $joinClause) use ($user) {
                $joinClause
                    ->on('er.exercise_id', '=', 'e.id')
                    ->where('er.user_id', '=', $user->id);
            })
            ->get()
            ->mapInto(UserExercise::class);
    }
}
