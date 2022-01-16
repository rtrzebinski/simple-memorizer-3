<?php

namespace App\Http\Requests;

use App\Models\Lesson;
use Illuminate\Auth\Access\Response;

/**
 * @property mixed lesson_id
 */
class PatchLessonRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return Response
     */
    public function authorize(): Response
    {
        return $this->gate()->authorize('modify', $this->route('lesson'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'visibility' => 'in:' . implode(',', Lesson::VISIBILITIES),
            'name' => 'string',
        ];
    }
}
