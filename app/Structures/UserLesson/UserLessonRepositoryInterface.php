<?php

namespace App\Structures\UserLesson;

/**
 * UserLesson operations valid for all (authenticated and guest) users
 *
 * Interface UserLessonRepositoryInterface
 * @package App\Structures\UserLesson
 */
interface UserLessonRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return UserLesson|null
     */
    public function fetchUserLesson(int $lessonId): ?UserLesson;
}
