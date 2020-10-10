<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use App\Structures\UserLesson\UserLesson;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class WebController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * User authenticated via web interface.
     * @return User|Authenticatable
     */
    protected function user()
    {
        return Auth::guard('web')->user();
    }

    /**
     * @param UserLesson|null $userLesson
     * @return array
     */
    protected function lessonViewData(UserLesson $userLesson = null): array
    {
        $canSubscribe = !$userLesson->is_subscriber;
        $canNotSubscribe = $userLesson->is_subscriber;
        // owner can not unsubscribe
        $canUnsubscribe = $userLesson->user_id && $userLesson->is_subscriber && $userLesson->owner_id != $userLesson->user_id;

        $canLearn = Gate::forUser($this->user())->allows('learn', $userLesson);

        // only owner can modify
        $canModify = $userLesson->owner_id == $userLesson->user_id;

        return [
            'userLesson' => $userLesson,
            'user' => $this->user(),
            'bidirectional' => $userLesson->is_bidirectional,
            'percentOfGoodAnswers' => $userLesson->percent_of_good_answers,
            'numberOfExercises' => $userLesson->exercises_count,
            'childLessonsCount' => $userLesson->child_lessons_count,
            // exclude owner from numberOfSubscribers display
            'numberOfSubscribers' => $userLesson->subscribers_count - 1,
            'canSubscribe' => $canSubscribe,
            'canNotSubscribe' => $canNotSubscribe,
            'canUnsubscribe' => $canUnsubscribe,
            'canLearn' => $canLearn,
            'canModify' => $canModify,
        ];
    }
}
