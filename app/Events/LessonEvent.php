<?php

namespace App\Events;

use App\Models\Lesson;
use App\Models\User;

abstract class LessonEvent implements LessonEventInterface
{
    /**
     * Create a new event instance.
     *
     * @param Lesson $lesson
     * @param User $user
     */
    public function __construct(protected Lesson $lesson, protected User $user)
    {
    }

    public function lesson(): Lesson
    {
        return $this->lesson;
    }

    public function user(): User
    {
        return $this->user;
    }
}
