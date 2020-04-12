<?php

namespace App\Structures\UserLesson;

use Illuminate\Support\Collection;

interface AuthenticatedUserLessonRepositoryInterface extends AbstractUserLessonRepositoryInterface
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
