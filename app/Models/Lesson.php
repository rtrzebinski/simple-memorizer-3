<?php

namespace App\Models;

use App\Events\LessonSubscribed;
use App\Events\LessonUnsubscribed;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Lesson
 *
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $visibility
 * @property int $exercises_count
 * @property int $subscribers_count
 * @property int $child_lessons_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson[] $childLessons
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Exercise[] $exercises
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson[] $parentLessons
 * @property-read int|null $parent_lessons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $subscribedUsers
 * @property-read int|null $subscribed_users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereChildLessonsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereExercisesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereSubscribersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Lesson whereVisibility($value)
 */
class Lesson extends Model
{
    use HasFactory;

    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PRIVATE = 'private';

    const VISIBILITIES = [
        self::VISIBILITY_PUBLIC,
        self::VISIBILITY_PRIVATE,
    ];

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
        'exercises_count' => 'int',
        'subscribers_count' => 'int',
        'child_lessons_count' => 'int',
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
     * All exercises of a lesson, including exercises from aggregated lessons.
     * Note that exercises only from one level below current lesson are included,
     * so exercise of a child will be included, but exercise of grandchild will be not.
     * @return Collection|Exercise[]
     */
    public function allExercises(): Collection
    {
        $lessonIds = $this->childLessons()->pluck('id')->add($this->id);
        return Exercise::query()->whereIn('lesson_id', $lessonIds)->get();
    }

    /**
     * @return BelongsToMany
     */
    public function subscribedUsers()
    {
        return $this->belongsToMany(User::class)
            // required for percent_of_good_answers to be included in the result
            ->withPivot(['percent_of_good_answers', 'bidirectional', 'favourite'])
            ->withTimestamps();
    }

    /**
     * @param User $user
     */
    public function subscribe(User $user)
    {
        $this->subscribedUsers()->save($user);
        event(new LessonSubscribed($this, $user));
    }

    /**
     * @param User $user
     * @throws \Exception
     */
    public function unsubscribe(User $user)
    {
        // just in case as policy already prevents this
        if ($this->owner_id == $user->id) {
            throw new \Exception('Unable to unsubscribe owned lesson: ' . $this->id);
        }

        $this->subscribedUsers()->detach($user);
        event(new LessonUnsubscribed($this, $user));
    }
}
