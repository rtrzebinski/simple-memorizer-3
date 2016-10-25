<?php

namespace App\Models\User;

use App\Models\Lesson\Lesson;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait InteractsWithLessons
{
    /**
     * Lessons user subscribes.
     * @return BelongsToMany
     */
    public function subscribedLessons()
    {
        return $this->belongsToMany(Lesson::class);
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
     * Lessons user does not own, and does not subscribe.
     * @return EloquentCollection
     */
    public function availableLessons() : EloquentCollection
    {
        return Lesson::query()
            ->where('lessons.owner_id', '!=', $this->id)
            ->where('lessons.visibility', '=', 'public')
            ->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->where(function ($query) {
                $query
                    ->where('lesson_user.user_id', '!=', $this->id)
                    ->orWhereNull('lesson_user.user_id');
            })
            ->get();
    }

    /**
     * @return bool
     */
    public function hasOwnedOrSubscribedLessons() : bool
    {
        return (bool)($this->ownedLessons->count() + $this->subscribedLessons->count());
    }
}
