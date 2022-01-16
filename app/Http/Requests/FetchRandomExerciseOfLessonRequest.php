<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;

/**
 * @property mixed previous_exercise_id
 */
class FetchRandomExerciseOfLessonRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return Response
     */
    public function authorize(): Response
    {
        return $this->gate()->authorize('access', $this->route('lesson'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'previous_exercise_id' => 'exists:exercises,id'
        ];
    }
}
