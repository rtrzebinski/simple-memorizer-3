<?php

namespace App\Services;

use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\UserExercise\UserExercise;
use Exception;
use Illuminate\Support\Collection;

/**
 * Based on history of answers of a user,
 * find out which exercise should be present to him
 * in order to memorize the entire set in most effective way.
 *
 * Class LearningService
 * @package App\Services
 */
class LearningService
{
    private AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository;
    private PointsCalculator $pointsCalculator;
    private ArrayRandomizer $arrayRandomizer;

    /**
     * LearningService constructor.
     * @param AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
     * @param PointsCalculator $pointsCalculator
     * @param ArrayRandomizer $randomizationService
     */
    public function __construct(
        AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository,
        PointsCalculator $pointsCalculator,
        ArrayRandomizer $randomizationService,
    ) {
        $this->userExerciseRepository = $userExerciseRepository;
        $this->pointsCalculator = $pointsCalculator;
        $this->arrayRandomizer = $randomizationService;
    }

    /**
     * @param Collection $userExercises
     * @param int|null $previousExerciseId
     * @return UserExercise|null
     * @throws Exception
     */
    public function findUserExerciseToLearn(Collection $userExercises, int $previousExerciseId = null): ?UserExercise
    {
        // exclude previous exercise if provided
        if ($previousExerciseId) {
            $userExercises = $userExercises->filter(
                function (UserExercise $userExercise) use ($previousExerciseId) {
                    return $userExercise->exercise_id != $previousExerciseId;
                }
            );
        }

        // case here is exercise deleted by the owner during the lesson
        if ($userExercises->isEmpty()) {
            return null;
        }

        $keys = [];

        foreach ($userExercises as $key => $userExercise) {
            $points = $this->pointsCalculator->calculatePoints($userExercise);

            /*
             * Fill $keys array with exercises $key multiplied by number of points.
             * This way exercises with higher number of points (so lower user knowledge) have bigger chance to be returned.
             */
            for ($i = $points; $i > 0; $i--) {
                $keys[] = $key;
            }
        }

        // all exercises have 0 points - none should be returned (served)
        if (empty($keys)) {
            // but perhaps previous has some points? let's check
            if ($previousExerciseId) {
                $previousUserExercise = $this->userExerciseRepository->fetchUserExerciseOfExercise(
                    $previousExerciseId
                );

                $previousPoints = $this->pointsCalculator->calculatePoints($previousUserExercise);

                // if previous has points, but no other exercise have points,
                // let's keep serving previous until user says he knows it,
                // in which case null will be returned, and lesson terminated
                if ($previousPoints > 0) {
                    return $previousUserExercise;
                }
            }

            return null;
        }

        return $userExercises[$this->arrayRandomizer->randomArrayElement($keys)];
    }
}
