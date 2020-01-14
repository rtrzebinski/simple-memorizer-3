<?php

namespace App\Http\Controllers\Web;

use App\Structures\UserExerciseRepository;
use Illuminate\Http\Request;

class ExerciseSearchController extends Controller
{
    /**
     * @param Request                $request
     * @param UserExerciseRepository $userExerciseRepository
     * @return \Illuminate\View\View
     */
    public function searchForExercises(Request $request, UserExerciseRepository $userExerciseRepository)
    {
        $phrase = $request->get('phrase');

        if ($phrase) {
            $userExercises = $userExerciseRepository->fetchUserExercisesWithPhrase($this->user(), $phrase);
        } else {
            // no results for no phrase (empty string search or parameter missing)
            $userExercises = collect();
        }

        return view('exercises.search', [
            'userExercises' => $userExercises,
            'phrase' => $phrase,
        ]);
    }
}
