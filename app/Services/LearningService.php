<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Exceptions\NotEnoughExercisesException;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use App\Models\ExerciseResult;
use Illuminate\Support\Facades\DB;

class LearningService
{
    /**
     * Fetch random exercise of lesson
     *
     * Exercise that user knows less have more chance to be returned.
     * Exercise that user knows more have less chance to be returned.
     *
     * @param Lesson   $lesson
     * @param int      $userId
     * @param int|null $previousExerciseId
     * @return Exercise
     * @throws Exception
     * @throws NotEnoughExercisesException
     */
    public function fetchRandomExerciseOfLesson(Lesson $lesson, int $userId, int $previousExerciseId = null): Exercise
    {
        /** @var Exercise[]|Collection $exercises */
        $exercises = $lesson->all_exercises
            ->filter(function (Exercise $exercise) use ($previousExerciseId) {
                // filter out previously served exercise
                return $exercise->id != $previousExerciseId;
            });

        if ($exercises->count() == 1) {
            return $exercises->first();
        }

        if ($exercises->isEmpty()) {
            throw new NotEnoughExercisesException;
        }

        // eager loading alleviates the N + 1 query problem
        $exercises->load([
            'results' => function ($relation) use ($userId) {
                // only load exercise results of given user
                $relation->where('exercise_results.user_id', $userId);
            }
        ]);

        $tmp = [];
        foreach ($exercises as $exercise) {
            // fetch exercise results of given user only
            // this is just in case as eager loading above should had already filtered this
            $results = $exercise->results->filter(function ($item) use ($userId) {
                return $item->user_id == $userId;
            });

            if ($results->isEmpty()) {
                /*
                 * User has no answers for this exercise, so we know that percent of good answers is 0
                 */
                $percentOfGoodAnswers = 0;
            } else {
                /*
                 * Check percent of good answers of a user
                 */
                $percentOfGoodAnswers = $results->first()->percent_of_good_answers;
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
     * @param int $exerciseId
     * @param int $userId
     */
    public function handleGoodAnswer(int $exerciseId, int $userId)
    {
        $this->increaseNumberOfAnswersOfUser($exerciseId, $userId, 'number_of_good_answers');
    }

    /**
     * @param int $exerciseId
     * @param int $userId
     */
    public function handleBadAnswer(int $exerciseId, int $userId)
    {
        $this->increaseNumberOfAnswersOfUser($exerciseId, $userId, 'number_of_bad_answers');
    }

    /**
     * Calculate number of points
     *
     * 1 means highest familiarity with the answer.
     * 10 means lowest familiarity with the answer.
     *
     * @param int $percentOfGoodAnswers
     * @return int
     * @throws \Exception
     */
    private function calculateNumberOfPoints(int $percentOfGoodAnswers): int
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

    /**
     * @param int    $exerciseId
     * @param int    $userId
     * @param string $field
     */
    private function increaseNumberOfAnswersOfUser(int $exerciseId, int $userId, string $field)
    {
        $exerciseResult = ExerciseResult::whereExerciseId($exerciseId)->whereUserId($userId)->first();

        if (is_null($exerciseResult)) {
            // create new exercise result
            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $userId;
            $exerciseResult->exercise_id = $exerciseId;
            $exerciseResult->{$field} = 1;
            $exerciseResult->save();
        } else {
            // increase number of answers for existing exercise result
            DB::table('exercise_results')->where('id', '=', $exerciseResult->id)
                ->update([$field => DB::raw($field." + 1")]);
        }

        $exerciseResult->updatePercentOfGoodAnswers();
    }
}
