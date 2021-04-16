<?php


namespace App\Services;

use App\Structures\UserExercise\UserExercise;
use Carbon\Carbon;

class PointsCalculator
{
    /**
     * Calculate points for given exercise result.
     *
     * @param UserExercise $userExercise
     * @return int
     * @throws \Exception
     */
    public function calculatePoints(UserExercise $userExercise): int
    {
        // no answers at all
        if ($userExercise->number_of_good_answers == 0 && $userExercise->number_of_bad_answers == 0) {
            // give question without any answers highest chance to be served
            return 100;
        }

        /** @var Carbon|null $latestGoodAnswer */
        $latestGoodAnswer = $userExercise->latest_good_answer ? new Carbon($userExercise->latest_good_answer) : null;

        /** @var Carbon|null $latestBadAnswer */
        $latestBadAnswer = $userExercise->latest_bad_answer ? new Carbon($userExercise->latest_bad_answer) : null;

        // check for answers today first

        // user had both good and bad answers today
        if ($latestGoodAnswer instanceof Carbon && $latestBadAnswer instanceof Carbon && $latestGoodAnswer->isToday() && $latestBadAnswer->isToday()) {
            // if good answer was the most recent - return 0 point to not bother user with this question anymore today
            // it makes more sense to serve it another day than serve again today
            if ($latestGoodAnswer->isAfter($latestBadAnswer)) {
                return 0;
            }
        }

        // user had just good answer today
        if ($latestGoodAnswer instanceof Carbon && $latestGoodAnswer->isToday()) {
            // return 0 point to not bother user with this question anymore today
            // it makes more sense to serve it another day than serve again today
            return 0;
        }

        // user had just bad answers today
        if ($latestBadAnswer instanceof Carbon && $latestBadAnswer->isToday()) {
            // first check whether 'max_exercise_bad_answers_per_day' was reached
            // if was return 0 points, so exercise is not served
            if ($userExercise->number_of_bad_answers_today >= config('app.max_exercise_bad_answers_per_day')) {
                return 0;
            }

            // important for new lessons with many new exercises, case of a new exercise user never knew:
            // give it double max points to it has a chance to be served couple times during first session it appeared
            // better to serve less exercises few times more, than show a lot just once
            if ($userExercise->number_of_good_answers == 0) {
                return 200;
            }

            // here we decrease points with incoming bad answers today
            // so user is not overloaded with this question
            // but he still see it once a while
            if ($userExercise->number_of_bad_answers_today == 1) {
                return 80;
            }

            if ($userExercise->number_of_bad_answers_today == 2) {
                return 50;
            }

            if ($userExercise->number_of_bad_answers_today >= 3) {
                return 20;
            }
        }

        // no answers today - check for answers of just one type

        // only good answers exist, but none today
        if ($userExercise->number_of_bad_answers == 0 && $userExercise->number_of_good_answers > 0) {
            if ($userExercise->number_of_good_answers == 1) {
                return 80;
            }

            if ($userExercise->number_of_good_answers == 2) {
                return 50;
            }

            if ($userExercise->number_of_good_answers == 3) {
                return 20;
            }

            return 1;
        }

        // no answers with just one type - calculate points

        // calculate points based on percent of good answers, so if user did not answer this exercise today,
        // we want to calculate points based on the ration of previous good and bad answers
        return $this->convertPercentOfGoodAnswersToPoints($userExercise->percent_of_good_answers);
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
