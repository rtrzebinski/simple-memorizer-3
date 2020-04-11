<?php

namespace App\Structures;

use App\Models\User;

interface UserLessonRepositoryInterface
{
    /**
     * @param User|null $user
     * @param int       $lessonId
     * @return UserLesson|null
     */
    public function fetchUserLesson(?User $user, int $lessonId): ?UserLesson;
}
