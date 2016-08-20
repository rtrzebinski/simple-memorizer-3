<?php

namespace App\Http\Requests;

use App\Models\Exercise\ExerciseRepositoryInterface;

/**
 * @property mixed exercise_id
 */
class DeleteExerciseRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param ExerciseRepositoryInterface $exerciseRepository
     * @return bool
     */
    public function authorize(ExerciseRepositoryInterface $exerciseRepository)
    {
        return $exerciseRepository->authorizeDeleteExercise($this->userId(), $this->route('exercise_id'));
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
