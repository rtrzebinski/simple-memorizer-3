<?php

namespace App\Http\Requests;

/**
 * @property array|int[] aggregates
 */
class SyncLessonAggregateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->gate()->authorize('modify', $this->route('parentLesson'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'aggregates' => 'required|array',
        ];
    }
}
