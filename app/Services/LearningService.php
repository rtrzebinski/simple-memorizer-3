<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseResult;
use App\Models\Lesson;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Based on history of answers of a user,
 * find out which exercise should be present to him
 * in order to memorize the entire lesson in most effective way.
 *
 * Class LearningService
 * @package App\Services
 */
class LearningService
{
    /**
     * Fetch pseudo random exercise of lesson. Should be served to a user in learning mode.
     *
     * @param Lesson   $lesson
     * @param int      $userId
     * @param int|null $previousExerciseId
     * @return Exercise|null
     * @throws Exception
     */
    public function fetchRandomExerciseOfLesson(Lesson $lesson, int $userId, int $previousExerciseId = null): ?Exercise
    {
        // All exercises of a lesson, including exercises from aggregated lessons.
        $exercises = $lesson->allExercises()
            ->filter(function (Exercise $exercise) use ($previousExerciseId) {
                // exclude previous exercise
                return $exercise->id != $previousExerciseId;
            });

        // Eager load ExerciseResults (alleviates the N + 1 query problem)
        $exercises->load([
            'results' => function (Relation $relation) use ($userId) {
                // only load exercise results of given user
                $relation->where('exercise_results.user_id', $userId);
            }
        ]);

        if ($exercises->isEmpty()) {
            return null;
        }

        if ($exercises->count() == 1) {
            return $exercises->first();
        }

        $tmp = [];

        foreach ($exercises as $key => $exercise) {
            // extra check - ensure only results of single user are loaded
            // results count might be 1 if user has ever answered an exercise or 0 otherwise
            assert(count($exercise->results) <= 1, 'Some unexpected results were loaded');

            $exerciseResult = $exercise->results->first();

            $points = $this->calculatePoints($exerciseResult);

            /*
             * Fill $tmp array with exercises $key multiplied by number of points.
             * This way exercises with higher number of points (so lower user knowledge) have bigger chance to be returned.
             */
            for ($i = $points; $i > 0; $i--) {
                $tmp[] = $key;
            }
        }

        /**
         * get a random $key and return matching exercise
         * @var Exercise $winner
         */
        $winner = $exercises[$tmp[array_rand($tmp)]];

        // if lesson is bidirectional flip question and answer with 50% chance
        if ($lesson->isBidirectional($userId) && rand(0, 1) == 1) {
            $flippedWinner = clone $winner;
            $flippedWinner->question = $winner->answer;
            $flippedWinner->answer = $winner->question;
            return $flippedWinner;
        }

        return $winner;
    }

    /**
     * Calculate points for given exercise result.
     *
     * @param ExerciseResult|null $exerciseResult
     * @return int
     * @throws Exception
     */
    public function calculatePoints(?ExerciseResult $exerciseResult): int
    {
        if (!$exerciseResult instanceof ExerciseResult) {
            // user has no answers for this exercise - it needs maximum number of points,
            // so exercise is very likely to be served
            return 100;
        }

        /** @var Carbon|null $latestGoodAnswer */
        $latestGoodAnswer = $exerciseResult->latest_good_answer;

        /** @var Carbon|null $latestBadAnswer */
        $latestBadAnswer = $exerciseResult->latest_bad_answer;

        // user had both good and bad answers today
        if ($latestGoodAnswer instanceof Carbon && $latestBadAnswer instanceof Carbon && $latestGoodAnswer->isToday() && $latestBadAnswer->isToday()) {

            // if good answer was the most recent - return 1 point to not bother user with this question anymore today
            // it makes more sense to serve it another day than serve again today
            if ($latestGoodAnswer->isAfter($latestBadAnswer)) {
                return 1;
            }

            // if bad answer was the most recent - return 100 points so it's likely it will be served today again
            // this will speed up memorizing of this particular exercise
            if ($latestBadAnswer->isAfter($latestGoodAnswer)) {
                return 100;
            }
        }

        // user had just good answer today
        if ($latestGoodAnswer instanceof Carbon && $latestGoodAnswer->isToday()) {
            // return 1 point to not bother user with this question anymore today
            // it makes more sense to serve it another day than serve again today
            return 1;
        }

        // user had just bad answer today
        if ($latestBadAnswer instanceof Carbon && $latestBadAnswer->isToday()) {
            // return 100 points so it's likely it will be served today again
            // this will speed up memorizing of this particular exercise
            return 100;
        }

        // calculate points based on percent of good answers, so if user did not answer this exercise today,
        // we want to calculate points based on the ration of previous good and bad answers
        return $this->convertPercentOfGoodAnswersToPoints($exerciseResult->percent_of_good_answers);
    }

    /**
     * Will return number of points related to percent of good answer.
     * For percent of good answer = 0 return 100 points (maximum).
     * For percent of good answer = 100 return 1 point (minimum).
     * For percent of good answer = 20 return 80 points.
     * For percent of good answer = 50 return 50 points.
     * For percent of good answer = 90 return 10 points.
     * etc.
     *
     * @param int $percentOfGoodAnswers
     * @return int
     * @throws \Exception
     */
    private function convertPercentOfGoodAnswersToPoints(int $percentOfGoodAnswers): int
    {
        if ($percentOfGoodAnswers == 100) {
            return 1;
        }

        return (100 - $percentOfGoodAnswers);
    }
}
