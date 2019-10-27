<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExerciseResult
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property int                             $exercise_id
 * @property int                             $number_of_good_answers
 * @property int                             $number_of_good_answers_today
 * @property \Illuminate\Support\Carbon|null $latest_good_answer
 * @property int                             $number_of_bad_answers
 * @property int                             $number_of_bad_answers_today
 * @property \Illuminate\Support\Carbon|null $latest_bad_answer
 * @property int                             $percent_of_good_answers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult user($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereExerciseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereLatestBadAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereLatestGoodAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereNumberOfBadAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereNumberOfBadAnswersToday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereNumberOfGoodAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereNumberOfGoodAnswersToday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult wherePercentOfGoodAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ExerciseResult whereUserId($value)
 */
class ExerciseResult extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'latest_good_answer',
        'latest_bad_answer',
    ];

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
