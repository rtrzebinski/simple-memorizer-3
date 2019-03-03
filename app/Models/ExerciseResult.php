<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExerciseResult
 *
 * @property int                 $id
 * @property int                 $user_id
 * @property int                 $exercise_id
 * @property int                 $number_of_good_answers
 * @property int                 $number_of_bad_answers
 * @property int                 $percent_of_good_answers
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult user($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereExerciseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereNumberOfBadAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereNumberOfGoodAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult wherePercentOfGoodAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereUserId($value)
 * @mixin Eloquent
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
    private function calculatePercentOfGoodAnswers(): int
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
