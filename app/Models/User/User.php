<?php

namespace App\Models\User;

use App\Models\Lesson\Lesson;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User\User
 *
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $api_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereApiToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User\User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
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

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class);
    }
}
