<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;

/**
 * @property array|int[] aggregates
 */
class SyncLessonAggregateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return Response
     */
    public function authorize(): Response
    {
        return $this->gate()->authorize('modify', $this->route('parentLesson'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'aggregates' => 'array',
        ];
    }
}
