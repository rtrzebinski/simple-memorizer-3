<?php

namespace App\Structures\UserLesson;

use App\Structures\AbstractStructure;

/**
 * Lesson details for a given User
 *
 * Class UserLesson
 * @package App\Structures\UserLesson
 */
class UserLesson extends AbstractStructure
{
    public ?int $user_id = null;
    public ?int $lesson_id = null;
    public ?int $owner_id = null;
    public ?string $name = null;
    public ?bool $is_bidirectional = null;
    public ?string $visibility = null;
    public ?int $exercises_count = null;
    public ?int $percent_of_good_answers = null;
    public ?int $subscribers_count = null;
    public ?int $child_lessons_count = null;
    public ?int $is_subscriber = null;
}
