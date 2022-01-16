<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;

/**
 * @property string redirect_to
 */
class UpdateExerciseRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return Response
     */
    public function authorize(): Response
    {
        return $this->gate()->authorize('modify', $this->route('exercise'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'question' => 'required|string',
            'answer' => 'required|string',
            'redirect_to' => 'string',
        ];
    }
}
