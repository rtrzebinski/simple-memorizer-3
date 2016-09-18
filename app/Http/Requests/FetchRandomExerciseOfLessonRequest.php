<?php

namespace App\Http\Requests;

/**
 * @property mixed previous_exercise_id
 */
class FetchRandomExerciseOfLessonRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->gate()->authorize('fetchExercisesOfLesson', $this->route('lesson'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'previous_exercise_id' => 'exists:exercises,id'
        ];
    }
}
