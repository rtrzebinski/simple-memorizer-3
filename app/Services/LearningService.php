<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseResult;
use App\Models\Lesson;
use Exception;

class LearningService
{
    /**
     * Fetch pseudo random exercise of lesson
     *
     * Exercise that user knows less have more chance to be returned.
     * Exercise that user knows more have less chance to be returned.
     *
     * @param Lesson   $lesson
     * @param int      $userId
     * @param int|null $previousExerciseId
     * @return Exercise|null
     * @throws Exception
     */
    public function fetchRandomExerciseOfLesson(Lesson $lesson, int $userId, int $previousExerciseId = null): ?Exercise
    {
        $exercises = $lesson->exercisesForGivenUser($userId)
            ->filter(function (Exercise $exercise) use ($previousExerciseId) {
                return $exercise->id != $previousExerciseId;
            });

        if ($exercises->isEmpty()) {
            return null;
        }

        if ($exercises->count() == 1) {
            return $exercises->first();
        }

        // if lesson is bidirectional
        // clone each exercise with reversed question and answer
        // so both variants are in the collection
        if ($lesson->isBidirectional($userId)) {
            foreach ($exercises as $exercise) {
                $clonedExercise = clone $exercise;
                $clonedExercise->question = $exercise->answer;
                $clonedExercise->answer = $exercise->question;
                $exercises->add($clonedExercise);
            }
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

        // get a random $key and return matching exercise
        return $exercises[$tmp[array_rand($tmp)]];
    }

    /**
     * Calculate points for given exercise result.
     *
     * @param ExerciseResult|null $exerciseResult
     * @return int
     * @throws Exception
     */
    private function calculatePoints(?ExerciseResult $exerciseResult): int
    {
        if (!$exerciseResult instanceof ExerciseResult) {
            // user has no answers for this exercise - it needs maximum number of points,
            // so exercise is very likely to be served
            return 1;
        }

        // calculate points based on percent of good answers
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
