<?php

namespace App\Http\Controllers\Web;

use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ExerciseSearchWebController extends WebController
{
    /**
     * @param Request $request
     * @param AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository
     * @return View
     */
    public function searchForExercises(
        Request $request,
        AuthenticatedUserExerciseRepositoryInterface $userExerciseRepository,
    ): View {
        $phrase = $request->get('phrase');

        if ($phrase) {
            $userExercises = $userExerciseRepository->fetchUserExercisesWithPhrase($phrase);
        } else {
            // no results for no phrase (empty string search or parameter missing)
            $userExercises = collect();
        }

        return view(
            'exercises.search',
            [
                'userExercises' => $userExercises,
                'phrase' => $phrase,
            ]
        );
    }
}
