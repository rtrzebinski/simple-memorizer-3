<?php

namespace App\Http\Controllers\Web;

use App\Events\LessonAggregatesUpdated;
use App\Http\Requests\SyncLessonAggregateRequest;
use App\Models\Lesson;
use App\Structures\UserLessonRepositoryInterface;
use Illuminate\View\View;
use \Illuminate\Http\RedirectResponse;

class LessonAggregateController extends Controller
{
    /**
     * @param Lesson                        $parentLesson
     * @param UserLessonRepositoryInterface $userLessonRepository
     * @return View
     */
    public function index(Lesson $parentLesson, UserLessonRepositoryInterface $userLessonRepository)
    {
        // all lessons owned by user
        $ownedLessons = $this->user()->ownedLessons;

        // lessons aggregated to current lesson
        $childLessons = $parentLesson->childLessons;

        $lessons = [];

        // check intersection between all lessons owned by user and aggregated lessons
        // mark aggregated lessons, so checkboxes are checked next to these on the UI
        foreach ($ownedLessons as &$ownedLesson) {

            // skip current lesson, impossible to aggregate itself
            if ($ownedLesson->id == $parentLesson->id) {
                continue;
            }

            $row = [];
            $row['id'] = $ownedLesson->id;
            $row['name'] = $ownedLesson->name;
            $row['is_aggregated'] = false;

            foreach ($childLessons as $la) {
                if ($ownedLesson->id == $la->id) {
                    $row['is_aggregated'] = true;
                }
            }

            $lessons[] = $row;
        }

        $userLesson = $userLessonRepository->fetchUserLesson($parentLesson->id);

        return view('lessons.aggregate', [
                'lessons' => $lessons,
            ] + $this->lessonViewData($userLesson));
    }

    /**
     * @param Lesson                     $parentLesson
     * @param SyncLessonAggregateRequest $request
     * @return RedirectResponse
     */
    public function sync(Lesson $parentLesson, SyncLessonAggregateRequest $request)
    {
        $parentLesson->childLessons()->sync($request->aggregates);

        event(new LessonAggregatesUpdated($parentLesson, $this->user()));

        return redirect('/lessons/aggregate/'.$parentLesson->id);
    }
}
