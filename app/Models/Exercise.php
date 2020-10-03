<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Exercise
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property int $lesson_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lesson $lesson
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ExerciseResult[] $results
 * @property-read int|null $results_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereLessonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Exercise whereUpdatedAt($value)
 */
class Exercise extends Model
{
    use HasFactory;

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
}
