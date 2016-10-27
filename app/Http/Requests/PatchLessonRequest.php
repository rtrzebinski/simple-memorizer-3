<?php

namespace App\Http\Requests;

/**
 * @property mixed lesson_id
 */
class PatchLessonRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->gate()->authorize('modify', $this->route('lesson'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'visibility' => 'in:public,private',
            'name' => 'string',
        ];
    }
}