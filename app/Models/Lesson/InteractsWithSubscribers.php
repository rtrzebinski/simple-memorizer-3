<?php

namespace App\Models\Lesson;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait InteractsWithSubscribers
{
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
