<?php

namespace App\Http\Requests;

use App\Models\Lesson\LessonRepositoryInterface;

/**
 * @property mixed lesson_id
 */
class UpdateLessonRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param LessonRepositoryInterface $lessonRepository
     * @return bool
     */
    public function authorize(LessonRepositoryInterface $lessonRepository)
    {
        return $lessonRepository->authorizeUpdateLesson($this->userId(), $this->route('lesson_id'));
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
