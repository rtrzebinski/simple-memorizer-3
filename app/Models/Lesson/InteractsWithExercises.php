<?php

namespace App\Models\Lesson;

use App\Exceptions\NotEnoughExercisesException;
use App\Models\Exercise\Exercise;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait InteractsWithExercises
{
    /**
     * @return HasMany
     */
    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

    /**
     * Fetch random exercise
     *
     * Exercise that user knows less have more chance to be returned.
     * Exercise that user knows more have less chance to be returned.
     *
     * @param int $userId
     * @param int|null $previousExerciseId
     * @return Exercise
     * @throws Exception
     * @throws NotEnoughExercisesException
     */
    public function fetchRandomExercise(int $userId, int $previousExerciseId = null) : Exercise
    {
        /** @var Exercise[]|Collection $exercises */
        $exercises = Exercise::select('exercises.*')
            ->with('results')
            ->join('lessons', 'lessons.id', '=', 'exercises.lesson_id')
            ->where('lessons.id', '=', $this->id)
            ->where('exercises.id', '!=', $previousExerciseId)
            ->get();

        if ($exercises->count() == 1) {
            return $exercises->first();
        }

        if ($exercises->isEmpty()) {
            throw new NotEnoughExercisesException;
        }

        $tmp = [];
        foreach ($exercises as $exercise) {
            if ($exercise->results->isEmpty()) {
                /*
                 * If results relation is not loaded, that means user has no answers for this exercises yet,
                 * so we know that percent of good answers is 0
                 */
                $percentOfGoodAnswers = 0;
            } else {
                /*
                 * Using already loaded relation (so no extra db call is required) check percent of good answers
                 * of a user
                 */
                $percentOfGoodAnswers = $exercise->results->filter(function ($item) use ($userId) {
                    return $item->user_id == $userId;
                })->first()->percent_of_good_answers;
            }

            /*
             * Fill $tmp array with $exercises multiplied by number of points.
             *
             * This way exercises with higher number of points (so lower user knowledge),
             * will have bigger chance to be returned.
             */
            for ($i = $this->calculateNumberOfPoints($percentOfGoodAnswers); $i > 0; $i--) {
                $tmp[] = $exercise;
            }
        }

        // do randomization
        shuffle($tmp);
        return $tmp[array_rand($tmp)];
    }

    /**
     * Calculate number of points
     *
     * 1 means highest familiarity with the answer.
     * 10 means lowest familiarity with the answer.
     *
     * @param int $percentOfGoodAnswers
     * @return int
     * @throws Exception
     */
    public function calculateNumberOfPoints(int $percentOfGoodAnswers) : int
    {
        if ($percentOfGoodAnswers <= 100 && $percentOfGoodAnswers > 90) {
            return 1;
        }
        if ($percentOfGoodAnswers <= 90 && $percentOfGoodAnswers > 80) {
            return 2;
        }
        if ($percentOfGoodAnswers <= 80 && $percentOfGoodAnswers > 70) {
            return 3;
        }
        if ($percentOfGoodAnswers <= 70 && $percentOfGoodAnswers > 60) {
            return 4;
        }
        if ($percentOfGoodAnswers <= 60 && $percentOfGoodAnswers > 50) {
            return 5;
        }
        if ($percentOfGoodAnswers <= 50 && $percentOfGoodAnswers > 40) {
            return 6;
        }
        if ($percentOfGoodAnswers <= 40 && $percentOfGoodAnswers > 30) {
            return 7;
        }
        if ($percentOfGoodAnswers <= 30 && $percentOfGoodAnswers > 20) {
            return 8;
        }
        if ($percentOfGoodAnswers <= 20 && $percentOfGoodAnswers > 10) {
            return 9;
        }
        if ($percentOfGoodAnswers <= 10 && $percentOfGoodAnswers >= 0) {
            return 10;
        }
        throw new Exception('$percentOfGoodAnswers must be a value between 0 and 100');
    }
}
