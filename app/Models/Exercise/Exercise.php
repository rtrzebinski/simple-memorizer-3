<?php

namespace App\Models\Exercise;

use App\Models\Lesson\Lesson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Exercise\Exercise
 *
 * @property integer $id
 * @property string $question
 * @property string $answer
 * @property integer $lesson_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Lesson\Lesson $lesson
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereQuestion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereAnswer($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereLessonId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereUpdatedAt($value)
 * @mixin \Eloquent
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
     * @return BelongsTo
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
