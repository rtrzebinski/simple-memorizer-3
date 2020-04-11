<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\User;
use App\Structures\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\UserExerciseRepositoryInterface;
use App\Structures\UserExercise;
use App\Structures\UserLesson;
use Carbon\Carbon;
use Exception;

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
    private UserExerciseRepositoryInterface $userExerciseRepository;
    private AuthenticatedUserExerciseRepositoryInterface $authenticatedUserExerciseRepository;

    /**
     * LearningService constructor.
     * @param UserExerciseRepositoryInterface              $userExerciseRepository
     * @param AuthenticatedUserExerciseRepositoryInterface $authenticatedUserExerciseRepository
     */
    public function __construct(UserExerciseRepositoryInterface $userExerciseRepository, AuthenticatedUserExerciseRepositoryInterface $authenticatedUserExerciseRepository)
    {
        $this->userExerciseRepository = $userExerciseRepository;
        $this->authenticatedUserExerciseRepository = $authenticatedUserExerciseRepository;
    }

    /**
     * Fetch pseudo random exercise of lesson. Should be served to a user in learning mode.
     *
     * @param UserLesson $userLesson
     * @param User       $user
     * @param int|null   $previousExerciseId
     * @return Exercise|null
     * @throws Exception
     */
    public function fetchRandomExerciseOfLesson(UserLesson $userLesson, User $user, int $previousExerciseId = null): ?UserExercise
    {
        $userExercises = $this->userExerciseRepository->fetchUserExercisesOfLesson($user, $userLesson->lesson_id)
            ->filter(function (UserExercise $userExercise) use ($previousExerciseId) {
                // exclude previous exercise
                return $userExercise->exercise_id != $previousExerciseId;
            });

        // case here is exercise deleted by the owner during the lesson
        if ($userExercises->isEmpty()) {
            return null;
        }

        $tmp = [];

        foreach ($userExercises as $key => $userExercise) {
            $points = $this->calculatePoints($userExercise);

            /*
             * Fill $tmp array with exercises $key multiplied by number of points.
             * This way exercises with higher number of points (so lower user knowledge) have bigger chance to be returned.
             */
            for ($i = $points; $i > 0; $i--) {
                $tmp[] = $key;
            }
        }

        // all exercises have 0 points - none should be returned (served)
        if (empty($tmp)) {
            // but perhaps previous has some points? let's check
            if ($previousExerciseId) {

                $previousUserExercise = $this->authenticatedUserExerciseRepository->fetchUserExerciseOfExercise($user, $previousExerciseId);

                $previousPoints = $this->calculatePoints($previousUserExercise);

                // if previous has points, but no other exercise have points,
                // let's keep serving previous until user says he knows it,
                // in which case null will be returned, and lesson terminated
                if ($previousPoints > 0) {
                    return $previousUserExercise;
                }
            }

            return null;
        }

        /**
         * get a random $key and return matching exercise
         * @var Exercise $winner
         */
        $winner = $userExercises[$tmp[array_rand($tmp)]];

        // if lesson is bidirectional flip question and answer with 50% chance
        if ($userLesson->is_bidirectional && rand(0, 1) == 1) {
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
     * @param UserExercise $userExercise
     * @return int
     * @throws Exception
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
