<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use App\Structures\UserLesson\UserLesson;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use League\Csv\Writer;
use SplTempFileObject;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * User authenticated via web interface.
     * @return User
     */
    protected function user()
    {
        return Auth::guard('web')->user();
    }

    /**
     * @return Writer
     */
    protected function createCsvWriter(): Writer
    {
        //the CSV file will be created using a temporary File
        $writer = Writer::createFromFileObject(new SplTempFileObject);
        //the delimiter will be the tab character
        $writer->setDelimiter(",");
        //use windows line endings for compatibility with some csv libraries
        $writer->setNewline("\r\n");
        return $writer;
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
