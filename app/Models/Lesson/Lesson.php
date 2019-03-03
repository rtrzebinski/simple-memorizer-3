<?php

namespace App\Models\Lesson;

use App\Models\User\User;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Lesson\Lesson
 *
 * @property integer                                                                       $id
 * @property integer                                                                       $owner_id
 * @property string                                                                        $name
 * @property string                                                                        $visibility
 * @property \Carbon\Carbon                                                                $created_at
 * @property \Carbon\Carbon                                                                $updated_at
 * @property-read \App\Models\User\User                                                    $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User\User[]         $subscribers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Exercise\Exercise[] $exercises
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereOwnerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereVisibility($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson\Lesson[] $lessonAggregate
 * @mixin Eloquent
 * @property-read Collection $all_exercises
 */
class Lesson extends Model
{
    use InteractsWithExercises;
    use InteractsWithSubscribers;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'visibility',
        'name',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'owner_id' => 'int',
    ];

    /**
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany|Lesson[]
     */
    public function lessonAggregate()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_aggregate', 'parent_lesson_id', 'child_lesson_id');
    }
}
