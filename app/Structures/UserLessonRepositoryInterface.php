<?php

namespace App\Structures;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserLessonRepositoryInterface
{
    /**
     * @param User|null $user
     * @param int       $lessonId
     * @return UserLesson|null
     */
    public function fetchUserLesson(?User $user, int $lessonId): ?UserLesson;

    /**
     * @param User $user
     * @return Collection
     */
    public function fetchOwnedUserLessons(User $user): Collection;

    /**
     * @param User $user
     * @return Collection
     */
    public function fetchSubscribedUserLessons(User $user): Collection;

    /**
     * @param User $user
     * @return Collection
     */
    public function fetchAvailableUserLessons(User $user): Collection;

    /**
     * @return Collection
     */
    public function fetchPublicUserLessons(): Collection;
}
