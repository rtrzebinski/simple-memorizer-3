<?php

namespace App\Models\Exercise;

use App\Models\ExerciseResult\ExerciseResult;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait InteractsWithExerciseResults
{
    /**
     * @return HasMany
     */
    public function results() : HasMany
    {
        return $this->hasMany(ExerciseResult::class);
    }

    /**
     * @param int $userId
     * @return int
     */
    public function numberOfGoodAnswersOfUser(int $userId) : int
    {
        $exerciseResult = $this->results()->user($userId)->first();
        return $exerciseResult ? $exerciseResult->number_of_good_answers : 0;
    }

    /**
     * @param int $userId
     * @return int
     */
    public function numberOfBadAnswersOfUser(int $userId) : int
    {
        $exerciseResult = $this->results()->user($userId)->first();
        return $exerciseResult ? $exerciseResult->number_of_bad_answers : 0;
    }

    /**
     * @param int $userId
     * @return int
     */
    public function percentOfGoodAnswersOfUser(int $userId) :int
    {
        $exerciseResult = $this->results()->user($userId)->first();
        return $exerciseResult ? $exerciseResult->percent_of_good_answers : 0;
    }

    /**
     * @param int $userId
     */
    public function increaseNumberOfGoodAnswersOfUser(int $userId)
    {
        $this->increaseNumberOfAnswersOfUser($userId, 'number_of_good_answers');
    }

    /**
     * @param int $userId
     */
    public function increaseNumberOfBadAnswersOfUser(int $userId)
    {
        $this->increaseNumberOfAnswersOfUser($userId, 'number_of_bad_answers');
    }

    /**
     * @param int $userId
     * @param string $field
     */
    private function increaseNumberOfAnswersOfUser(int $userId, string $field)
    {
        $exerciseResult = $this->results()->user($userId)->first();

        if (is_null($exerciseResult)) {
            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $userId;
            $exerciseResult->exercise_id = $this->id;
            $exerciseResult->{$field} = 1;
            $exerciseResult->save();
        } else {
            $exerciseResult->update([$field => \DB::raw($field . " + 1")]);
        }

        $exerciseResult->updatePercentOfGoodAnswers();
    }
}
