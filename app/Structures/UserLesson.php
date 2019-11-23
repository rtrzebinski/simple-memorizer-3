<?php

namespace App\Structures;

class UserLesson extends AbstractStructure
{
    /**
     * @var int
     */
    public $user_id;

    /**
     * @var int
     */
    public $lesson_id;

    /**
     * @var int
     */
    public $owner_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $is_bidirectional;

    /**
     * @var string
     */
    public $visibility;

    /**
     * @var int
     */
    public $exercises_count;

    /**
     * @var int
     */
    public $percent_of_good_answers;

    /**
     * @var int
     */
    public $subscribers_count;

    /**
     * @var int
     */
    public $child_lessons_count;

    /**
     * @var int
     */
    public $is_subscriber;
}
