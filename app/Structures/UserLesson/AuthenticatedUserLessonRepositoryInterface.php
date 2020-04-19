<?php

namespace App\Structures\UserLesson;

use Illuminate\Support\Collection;

/**
 * UserLesson operations valid for authenticated users only
 *
 * Interface AuthenticatedUserLessonRepositoryInterface
 * @package App\Structures\UserLesson
 */
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
}
