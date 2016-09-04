<?php

namespace App\Models\Lesson;

use App\Models\Exercise\Exercise;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Lesson\Lesson
 *
 * @property integer $id
 * @property integer $owner_id
 * @property string $name
 * @property string $visibility
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User\User[] $subscribers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Exercise\Exercise[] $exercises
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereOwnerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereVisibility($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Lesson\Lesson whereUpdatedAt($value)
 * @mixin \Eloquent
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
     * @return BelongsToMany
     */
    public function subscribers()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasMany
     */
    public function exercises()
    {
        return $this->hasMany(Exercise::class);
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
