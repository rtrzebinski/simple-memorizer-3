<?php

namespace App\Models\Lesson;

use App\Exceptions\NotEnoughExercisesException;
use App\Models\Exercise\Exercise;
use App\Models\Exercise\ExerciseRepository;
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
     * @return Collection
     */
    public function getAllExercisesAttribute()
    {
        /** @var Collection $allExercises */
        $allExercises = $this->exercises;

        foreach ($this->lessonAggregate as $lesson) {
            $allExercises = $allExercises->merge($lesson->exercises);
        }

        return $allExercises;
    }

    /**
     * @return ExerciseRepository
     */
    protected function exerciseRepository(): ExerciseRepository
    {
        return app(ExerciseRepository::class);
    }

    /**
     * @param int      $userId
     * @param int|null $previousExerciseId
     * @return Exercise
     * @throws NotEnoughExercisesException
     */
    public function fetchRandomExercise(int $userId, int $previousExerciseId = null): Exercise
    {
        return $this->exerciseRepository()->fetchRandomExerciseOfLesson($this, $userId, $previousExerciseId);
    }

    /**
     * @param int $percentOfGoodAnswers
     * @return int
     */
    public function calculateNumberOfPoints(int $percentOfGoodAnswers): int
    {
        return $this->calculateNumberOfPoints($percentOfGoodAnswers);
    }
}
