<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson;
use App\Models\User;
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
     * @param Lesson $lesson
     * @return array
     */
    protected function manageLessonViewData(Lesson $lesson): array
    {
        $canSubscribe = Gate::forUser($this->user())->allows('subscribe', $lesson);
        $canNotSubscribe = !$canSubscribe;
        $canUnsubscribe = Gate::forUser($this->user())->allows('unsubscribe', $lesson);
        $canLearn = Gate::forUser($this->user())->allows('learn', $lesson);
        $canModify = Gate::forUser($this->user())->allows('modify', $lesson);

        $subscriberPivot = $lesson->subscriberPivot($this->user()->id);

        return [
            'lesson' => $lesson,
            'threshold' => $subscriberPivot->threshold ?? null,
            'bidirectional' => ($subscriberPivot->bidirectional ?? null) ? 'yes' : 'no',
            'percentOfGoodAnswers' => $subscriberPivot->percent_of_good_answers ?? null,
            'numberOfExercises' => $lesson->allExercises()->count(),
            'numberOfActiveExercises' => $subscriberPivot ? $lesson->exercisesForGivenUser($this->user()->id)->count() : null,
            'numberOfAggregates' => $lesson->childLessons()->count(),
            'subscribedUsersWithOwnerExcluded' => $lesson->subscribedUsersWithOwnerExcluded()->count(),
            'canSubscribe' => $canSubscribe,
            'canNotSubscribe' => $canNotSubscribe,
            'canUnsubscribe' => $canUnsubscribe,
            'canLearn' => $canLearn,
            'canModify' => $canModify,
        ];
    }
}
