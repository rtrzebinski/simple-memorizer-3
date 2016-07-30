<?php

namespace App\Models\Lesson;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Lesson\Lesson
 *
 * @property integer $id
 * @property integer $owner_id
 * @property string $name
 * @property string $visibility
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
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
}
