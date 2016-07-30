<?php

namespace App\Http\Requests;

use App\Models\Lesson\Lesson;
use Illuminate\Database\Eloquent\Builder;
use Auth;

/**
 * @property mixed lesson_id
 */
class SubscribeLessonRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Lesson::whereId($this->route('lesson_id'))
            ->where(function (Builder $query) {
                $query->where('visibility', '=', 'public')
                    ->orWhere('owner_id', '=', Auth::guard('api')->user()->id);
            })->leftJoin('lesson_user', 'lesson_user.lesson_id', '=', 'lessons.id')
            ->whereNull('lesson_user.user_id')
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
