<?php

namespace App\Structures;

use App\Models\User;
use Illuminate\Support\Collection;

interface AuthenticatedUserLessonRepositoryInterface
{
    /**
     * @param User $user
     * @return Collection
     */
    public function fetchOwnedUserLessons(User $user): Collection;

    /**
     * @param User $user
     * @return Collection
     */
    public function fetchSubscribedUserLessons(User $user): Collection;

    /**
     * @param User $user
     * @return Collection
     */
    public function fetchAvailableUserLessons(User $user): Collection;
}
