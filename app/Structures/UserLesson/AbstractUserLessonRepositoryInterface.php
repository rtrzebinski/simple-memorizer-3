<?php

namespace App\Structures\UserLesson;

use Illuminate\Support\Collection;

/**
 * UserLesson operations valid for all users
 *
 * Interface UserLessonRepositoryInterface
 * @package App\Structures\UserLesson
 */
interface AbstractUserLessonRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return UserLesson|null
     */
    public function fetchUserLesson(int $lessonId): ?UserLesson;

    /**
     * @return Collection
     */
    public function fetchAvailableUserLessons(): Collection;
}
