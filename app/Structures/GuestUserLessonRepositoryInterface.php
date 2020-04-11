<?php

namespace App\Structures;

use Illuminate\Support\Collection;

interface GuestUserLessonRepositoryInterface extends UserLessonRepositoryInterface
{
    /**
     * @return Collection
     */
    public function fetchPublicUserLessons(): Collection;
}
