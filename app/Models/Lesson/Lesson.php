<?php

namespace App\Models\Lesson;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
