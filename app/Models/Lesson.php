<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Lesson
 *
 * @property int                                                                  $id
 * @property int                                                                  $owner_id
 * @property string                                                               $name
 * @property string                                                               $visibility
 * @property \Carbon\Carbon|null                                                  $created_at
 * @property \Carbon\Carbon|null                                                  $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Exercise[] $exercises
 * @property-read Collection                                                      $all_exercises
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson[]   $lessonAggregate
 * @property-read \App\Models\User                                                $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[]     $subscribers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereVisibility($value)
 * @mixin Eloquent
 */
class Lesson extends Model
{
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

    /**
     * @return HasMany
     */
    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

    /**
     * @return Collection
     */
    public function getAllExercisesAttribute()
    {
        /** @var Collection $allExercises */
        $allExercises = $this->exercises;

        foreach ($this->lessonAggregate as $lesson) {
            $allExercises = $allExercises->merge($lesson->exercises);
        }

        return $allExercises;
    }

    /**
     * @return BelongsToMany
     */
    public function subscribers()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @param User $user
     */
    public function subscribe(User $user)
    {
        $this->subscribers()->save($user);
    }

    /**
     * @param User $user
     */
    public function unsubscribe(User $user)
    {
        $this->subscribers()->detach($user);
    }
}
