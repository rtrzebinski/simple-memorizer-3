<?php

namespace App\Models\User;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User\User
 *
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $api_token
 * @property string $remember_token
 * @property string $auth_driver
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson\Lesson[] $subscribedLessons
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lesson\Lesson[] $ownedLessons
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $unreadNotifications
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereApiToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereAuthDriver($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;
    use InteractsWithLessons;

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
}
