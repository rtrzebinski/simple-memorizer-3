<?php

namespace App\Models\ExerciseResult;

use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExerciseResult\ExerciseResult
 *
 * @mixin \Eloquent
 * @property integer $id
 * @property integer $user_id
 * @property integer $exercise_id
 * @property integer $number_of_good_answers
 * @property integer $number_of_bad_answers
 * @property integer $percent_of_good_answers
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult whereExerciseId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult whereNumberOfGoodAnswers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult whereNumberOfBadAnswers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult wherePercentOfGoodAnswers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ExerciseResult\ExerciseResult user($userId)
 */
class ExerciseResult extends Model
{
    /**
     * Update object state and db row
     */
    public function updatePercentOfGoodAnswers()
    {
        $this->percent_of_good_answers = $this->calculatePercentOfGoodAnswers();
        DB::table('exercise_results')->where('id', '=', $this->id)
            ->update(['percent_of_good_answers' => $this->percent_of_good_answers]);
    }

    /**
     * @return int
     */
    private function calculatePercentOfGoodAnswers() : int
    {
        $totalNumberOfAnswers = $this->number_of_good_answers + $this->number_of_bad_answers;
        if ($totalNumberOfAnswers) {
            return round(100 * $this->number_of_good_answers / ($totalNumberOfAnswers));
        } else {
            return 0;
        }
    }

    /**
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeUser($query, $userId)
    {
        return $query->where('exercise_results.user_id', '=', $userId);
    }
}
