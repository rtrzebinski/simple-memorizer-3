<?php

namespace App\Events;

use App\Models\Lesson;
use App\Models\User;

abstract class ExerciseEvent implements LessonEventInterface
{
    /**
     * Create a new event instance.
     *
     * @param int $exerciseId
     * @param User $user
     */
    public function __construct(protected int $exerciseId, protected User $user,)
    {
    }

    public function user(): User
    {
        return $this->user;
    }

    public function exerciseId(): int
    {
        return $this->exerciseId;
    }

    public function lesson(): Lesson
    {
        return Lesson::query()
            ->select('l.*')
            ->from('lessons AS l')
            ->join('exercises AS e', 'e.lesson_id', '=', 'l.id')
            ->where('e.id', '=', $this->exerciseId)
            ->first();
    }
}
