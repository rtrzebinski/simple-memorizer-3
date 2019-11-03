<?php

namespace App\Structures;

class UserLesson extends AbstractStructure
{
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
}
