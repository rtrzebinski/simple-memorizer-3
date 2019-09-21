<?php

namespace App\Http\Controllers\Web;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class ExerciseSearchController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function searchForExercises(Request $request)
    {
        $phrase = $request->get('phrase');

        $exercises = Exercise::query()
            ->join('lessons', function (JoinClause $join) {
                $join->on('lessons.id', '=', 'exercises.lesson_id')
                    ->where('lessons.owner_id', '=', $this->user()->id);
            })
            ->where(function (Builder $builder) use ($phrase) {
                $builder->where('question', 'like', '%'.$phrase.'%')
                    ->orWhere('answer', 'like', '%'.$phrase.'%');
            })
            ->get();

        $data = [
            'exercises' => $exercises,
            'phrase' => $phrase
        ];

        return view('exercises.search', $data);
    }
}
