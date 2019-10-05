<?php

namespace App\Events;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\User;

abstract class ExerciseEvent implements LessonEventInterface
{
    /**
     * @var Exercise
     */
    protected $exercise;

    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new event instance.
     *
     * @param Exercise $exercise
     * @param User     $user
     */
    public function __construct(Exercise $exercise, User $user)
    {
        $this->exercise = $exercise;
        $this->user = $user;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function exercise(): Exercise
    {
        return $this->exercise;
    }

    public function lesson(): Lesson
    {
        return $this->exercise->lesson;
    }
}
