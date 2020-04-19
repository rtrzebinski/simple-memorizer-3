<?php

namespace App\Structures\UserExercise;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * UserExercise operations valid for guest users only
 *
 * Class GuestUserExerciseRepository
 * @package App\Structures\UserExercise
 */
class GuestUserExerciseRepository implements AbstractUserExerciseRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return Collection|UserExercise[]
     */
    public function fetchUserExercisesOfLesson(int $lessonId): Collection
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
                DB::raw('null AS number_of_good_answers'),
                DB::raw('null AS number_of_good_answers_today'),
                DB::raw('null AS latest_good_answer'),
                DB::raw('null AS number_of_bad_answers'),
                DB::raw('null AS number_of_bad_answers_today'),
                DB::raw('null AS latest_bad_answer'),
                DB::raw('null AS percent_of_good_answers'),
            ])
            ->joinSub($lessons, 'lessons', function (JoinClause $join) {
                $join
                    ->on('e.lesson_id', '=', 'lessons.child_id')
                    ->orOn('e.lesson_id', '=', 'lessons.lesson_id');
            })
            ->get()
            ->mapInto(UserExercise::class);
    }
}
