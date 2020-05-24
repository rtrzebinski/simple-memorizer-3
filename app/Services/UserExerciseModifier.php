<?php

namespace App\Services;

use App\Structures\UserExercise\UserExercise;
use InvalidArgumentException;

class UserExerciseModifier
{
    /**
     * Swap question with answer of given UserExercise.
     * @param UserExercise $userExercise
     * @param int          $probability
     * @return UserExercise
     * @throws InvalidArgumentException
     */
    public function swapQuestionWithAnswer(UserExercise $userExercise, int $probability): UserExercise
    {
        if ($probability < 0 || $probability > 100) {
            throw new InvalidArgumentException();
        }

        $shouldSwap = rand(1, 100) <= $probability;

        if ($shouldSwap) {
            $flipped = clone $userExercise;
            $flipped->question = $userExercise->answer;
            $flipped->answer = $userExercise->question;
            return $flipped;
        }

        return $userExercise;
    }
}
