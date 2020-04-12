<?php

namespace App\Structures\UserLesson;

interface AbstractUserLessonRepositoryInterface
{
    /**
     * @param int $lessonId
     * @return UserLesson|null
     */
    public function fetchUserLesson(int $lessonId): ?UserLesson;
}
