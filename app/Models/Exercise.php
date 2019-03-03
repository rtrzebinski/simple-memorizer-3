<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Exercise
 *
 * @property int                                                                        $id
 * @property string                                                                     $question
 * @property string                                                                     $answer
 * @property int                                                                        $lesson_id
 * @property \Carbon\Carbon|null                                                        $created_at
 * @property \Carbon\Carbon|null                                                        $updated_at
 * @property-read \App\Models\Lesson                                                    $lesson
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ExerciseResult[] $results
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereLessonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Exercise extends Model
{
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'question',
        'answer',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'lesson_id' => 'int',
    ];

    /**
     * @return BelongsTo
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(ExerciseResult::class);
    }

    /**
     * @param int $userId
     * @return ExerciseResult|null
     */
    public function resultOfUser(int $userId)
    {
        return $this->results()->where('exercise_results.user_id', $userId)->first();
    }

    /**
     * @param int $userId
     * @return int
     */
    public function numberOfGoodAnswersOfUser(int $userId): int
    {
        $exerciseResult = ExerciseResult::whereExerciseId($this->id)->whereUserId($userId)->first();
        return $exerciseResult ? $exerciseResult->number_of_good_answers : 0;
    }

    /**
     * @param int $userId
     * @return int
     */
    public function numberOfBadAnswersOfUser(int $userId): int
    {
        $exerciseResult = ExerciseResult::whereExerciseId($this->id)->whereUserId($userId)->first();
        return $exerciseResult ? $exerciseResult->number_of_bad_answers : 0;
    }

    /**
     * @param int $userId
     * @return int
     */
    public function percentOfGoodAnswersOfUser(int $userId): int
    {
        $exerciseResult = ExerciseResult::whereExerciseId($this->id)->whereUserId($userId)->first();
        return $exerciseResult ? $exerciseResult->percent_of_good_answers : 0;
    }
}
