<?php

namespace App\Http\Requests;

use App\Models\Lesson\Lesson;
use Auth;

/**
 * @property mixed lesson_id
 */
class DeleteLessonRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Lesson::whereId($this->route('lesson_id'))
            ->whereOwnerId(Auth::guard('api')->user()->id)
            ->exists();
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
