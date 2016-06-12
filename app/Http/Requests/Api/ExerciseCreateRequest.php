<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Request;

/**
 * Class ExerciseCreateRequest
 * @property mixed exercise_id
 * @package App\Http\Requests\Api
 */
class ExerciseCreateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question' => 'required',
            'answer' => 'required',
        ];
    }
}
