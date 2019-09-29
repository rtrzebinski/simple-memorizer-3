<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;

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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson[]   $childLessons
 * @property-read \App\Models\User                                                $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[]     $subscribers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereVisibility($value)
 * @mixin Eloquent
 * @property int                                                                  $bidirectional
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereBidirectional($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $subscribersWithOwnerExcluded
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson[] $parentLessons
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
        'bidirectional',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'owner_id' => 'int',
        'bidirectional' => 'bool',
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
    public function childLessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_aggregate', 'parent_lesson_id', 'child_lesson_id');
    }

    /**
     * @return BelongsToMany|Lesson[]
     */
    public function parentLessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_aggregate', 'child_lesson_id', 'parent_lesson_id');
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
        $allExercises = $this->exercises;

        foreach ($this->childLessons as $lesson) {
            $allExercises = $allExercises->merge($lesson->exercises);
        }

        return $allExercises;
    }

    /**
     * @return BelongsToMany
     */
    public function subscribers()
    {
        return $this->belongsToMany(User::class)
            // required for percent_of_good_answers to be included in the result
            ->withPivot(['percent_of_good_answers']);
    }

    /**
     * @return BelongsToMany
     */
    public function subscribersWithOwnerExcluded()
    {
        return $this->subscribers()
            // exclude lesson owner from subscribers
            ->join('lessons', function (JoinClause $joinClause) {
                $joinClause->on('lessons.id', '=', 'lesson_user.lesson_id')->on('lessons.owner_id', '!=', 'lesson_user.user_id');
            });
    }

    /**
     * @param int $userId
     * @return int
     * @throws \Exception
     */
    public function percentOfGoodAnswersOfUser(int $userId): int
    {
        // use relation so it can be eager loaded
        $subscriber = $this->subscribers()->where('lesson_user.user_id', $userId)->first();

        if (!$subscriber) {
            throw new \Exception('User does not subscribe lesson: '.$this->id);
        }

        return $subscriber->pivot->percent_of_good_answers;
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
     * @throws \Exception
     */
    public function unsubscribe(User $user)
    {
        // just in case as policy already prevents this
        if ($this->owner_id == $user->id) {
            throw new \Exception('Unable to unsubscribe owned lesson: '.$this->id);
        }

        $this->subscribers()->detach($user);
    }
}
