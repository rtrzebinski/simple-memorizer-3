<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Exercise
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $question
 * @property string $answer
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Exercise whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Exercise whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Exercise whereQuestion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Exercise whereAnswer($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Exercise whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Exercise whereUpdatedAt($value)
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
