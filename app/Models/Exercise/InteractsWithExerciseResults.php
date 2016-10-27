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
     * @return ExerciseRepository
     */
    protected function exerciseRepository() : ExerciseRepository
    {
        return app(ExerciseRepository::class);
    }

    /**
     * @param int $userId
     * @return int
     */
    public function numberOfGoodAnswersOfUser(int $userId) : int
    {
        return $this->exerciseRepository()->numberOfGoodAnswersOfUser($this->id, $userId);
    }

    /**
     * @param int $userId
     * @return int
     */
    public function numberOfBadAnswersOfUser(int $userId) : int
    {
        return $this->exerciseRepository()->numberOfBadAnswersOfUser($this->id, $userId);
    }

    /**
     * @param int $userId
     * @return int
     */
    public function percentOfGoodAnswersOfUser(int $userId) : int
    {
        return $this->exerciseRepository()->percentOfGoodAnswersOfUser($this->id, $userId);
    }

    /**
     * @param int $userId
     */
    public function handleGoodAnswer(int $userId)
    {
        $this->exerciseRepository()->handleGoodAnswer($this->id, $userId);
    }

    /**
     * @param int $userId
     */
    public function handleBadAnswer(int $userId)
    {
        $this->exerciseRepository()->handleBadAnswer($this->id, $userId);
    }
}
