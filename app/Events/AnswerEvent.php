<?php

namespace App\Events;

use App\Models\Exercise;

abstract class AnswerEvent
{
    /**
     * @var Exercise
     */
    public $exercise;

    /**
     * @var int
     */
    public $userId;

    /**
     * Create a new event instance.
     *
     * @param Exercise $exercise
     * @param int      $userId
     */
    public function __construct(Exercise $exercise, int $userId)
    {
        $this->exercise = $exercise;
        $this->userId = $userId;
    }
}
