<?php

namespace App\Structures\UserLesson;

use Illuminate\Support\Collection;

interface GuestUserLessonRepositoryInterface extends AbstractUserLessonRepositoryInterface
{
    /**
     * @return Collection
     */
    public function fetchPublicUserLessons(): Collection;
}
