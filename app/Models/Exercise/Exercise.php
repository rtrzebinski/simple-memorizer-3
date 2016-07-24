<?php

namespace App\Models\Exercise;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Exercise\Exercise
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $question
 * @property string $answer
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereQuestion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exercise\Exercise whereAnswer($value)
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
}
