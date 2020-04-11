<?php

namespace App\Structures;

use Illuminate\Support\Collection;

interface GuestUserLessonRepositoryInterface
{
    /**
     * @return Collection
     */
    public function fetchPublicUserLessons(): Collection;
}
