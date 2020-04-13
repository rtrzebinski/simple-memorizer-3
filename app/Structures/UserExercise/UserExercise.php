<?php

namespace App\Structures\UserExercise;

use App\Structures\AbstractStructure;

/**
 * Exercise details for a given User
 *
 * Class UserExercise
 * @package App\Structures\UserExercise
 */
class UserExercise extends AbstractStructure
{
    public ?int $exercise_id = null;
    public ?int $lesson_id = null;
    public ?string $lesson_name = null;
    public ?string $question = null;
    public ?string $answer = null;
    public ?int $number_of_good_answers = null;
    public ?int $number_of_good_answers_today = null;
    public ?string $latest_good_answer = null;
    public ?int $number_of_bad_answers = null;
    public ?int $number_of_bad_answers_today = null;
    public ?string $latest_bad_answer = null;
    public ?int $percent_of_good_answers = null;
}
