<?php

namespace App\Structures;

class UserExercise extends AbstractStructure
{
    /**
     * @var int
     */
    public $exercise_id;

    /**
     * @var int
     */
    public $lesson_id;

    /**
     * @var string
     */
    public $question;

    /**
     * @var string
     */
    public $answer;

    /**
     * @var int
     */
    public $number_of_good_answers;

    /**
     * @var int
     */
    public $number_of_good_answers_today;

    /**
     * @var string|null
     */
    public $latest_good_answer;

    /**
     * @var int
     */
    public $number_of_bad_answers;

    /**
     * @var int
     */
    public $number_of_bad_answers_today;

    /**
     * @var string|null
     */
    public $latest_bad_answer;

    /**
     * @var int
     */
    public $percent_of_good_answers;
}
