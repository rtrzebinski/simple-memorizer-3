<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Request;
use Auth;
use DB;

/**
 * Class DeleteExerciseRequest
 * @property mixed exercise_id
 * @package App\Http\Requests\Api
 */
class DeleteExerciseRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return DB::table('exercises')
            ->where('id', '=', $this->route('exercise_id'))
            ->where('user_id', '=', Auth::guard('api')->user()->id)
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
