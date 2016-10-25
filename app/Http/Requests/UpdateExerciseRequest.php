<?php

namespace App\Http\Requests;

/**
 * @property mixed exercise_id
 */
class UpdateExerciseRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->gate()->authorize('modify', $this->route('exercise'));
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
