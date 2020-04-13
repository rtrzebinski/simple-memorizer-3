<?php

namespace App\Structures\UserLesson;

use Illuminate\Support\Collection;

/**
 * UserLesson operations valid for guest users only
 *
 * Interface GuestUserLessonRepositoryInterface
 * @package App\Structures\UserLesson
 */
interface GuestUserLessonRepositoryInterface extends UserLessonRepositoryInterface
{
    /**
     * @return Collection
     */
    public function fetchPublicUserLessons(): Collection;
}
