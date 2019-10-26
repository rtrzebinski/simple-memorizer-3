<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * App\Models\User
 *
 * @property int                                                                                                            $id
 * @property string                                                                                                         $email
 * @property string                                                                                                         $password
 * @property string                                                                                                         $api_token
 * @property string|null                                                                                                    $remember_token
 * @property \Carbon\Carbon|null                                                                                            $created_at
 * @property \Carbon\Carbon|null                                                                                            $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson[]                                             $ownedLessons
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson[]                                             $subscribedLessons
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAuthDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'email',
    ];

    /**
     * The attributes that should be hidden for arrays.
     * @var array
     */
    protected $hidden = [
        'password',
        'api_token',
    ];

    /**
     * Lessons user subscribes.
     * @return BelongsToMany
     */
    public function subscribedLessons()
    {
        return $this->belongsToMany(Lesson::class)->where('lessons.owner_id', '!=', $this->id);
    }

    /**
     * Lessons user owns.
     * @return HasMany
     */
    public function ownedLessons()
    {
        return $this->hasMany(Lesson::class, 'owner_id');
    }

    /**
     * Public lessons user does not own and does not subscribe.
     * @return EloquentCollection
     */
    public function availableLessons(): EloquentCollection
    {
        return Lesson::query()
            ->where('lessons.owner_id', '!=', $this->id)
            ->where('lessons.visibility', '=', 'public')
            ->whereNotIn('lessons.id', $this->subscribedLessons()->pluck('lessons.id'))
            ->with('exercises', 'subscribedUsers')
            ->get();
    }

    /**
     * @return bool
     */
    public function hasOwnedOrSubscribedLessons(): bool
    {
        return (bool)($this->ownedLessons->count() + $this->subscribedLessons->count());
    }
}
