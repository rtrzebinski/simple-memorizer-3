<?php

namespace App\Structures;

use Illuminate\Support\Collection;

interface AuthenticatedUserLessonRepositoryInterface extends UserLessonRepositoryInterface
{
    /**
     * @return Collection
     */
    public function fetchOwnedUserLessons(): Collection;

    /**
     * @return Collection
     */
    public function fetchSubscribedUserLessons(): Collection;

    /**
     * @return Collection
     */
    public function fetchAvailableUserLessons(): Collection;
}
