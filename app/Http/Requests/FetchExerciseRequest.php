<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Auth;
use DB;

/**
 * @property mixed exercise_id
 */
class FetchExerciseRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return DB::table('exercises')
            ->where('exercises.id', '=', $this->route('exercise_id'))
            ->join('lessons', 'lessons.id', '=', 'exercises.lesson_id')
            ->where('lessons.owner_id', '=', Auth::guard('api')->user()->id)
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
