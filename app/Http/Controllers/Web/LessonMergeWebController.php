<?php

namespace App\Http\Controllers\Web;

use App\Events\ExercisesMerged;
use App\Models\Lesson;
use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;

class LessonMergeWebController extends WebController
{
    /**
     * @param Lesson $lesson
     * @param AuthenticatedUserLessonRepositoryInterface $userLessonRepository
     * @return View|Response
     */
    public function index(Lesson $lesson, AuthenticatedUserLessonRepositoryInterface $userLessonRepository): View|Response
    {
        // user is not the owner of the lesson
        if ($lesson->owner_id != $this->user()->id) {
            return response('This action is unauthorized', Response::HTTP_FORBIDDEN);
        }

        // all lessons owned by user
        $ownedLessons = $this->user()->ownedLessons;

        $lessons = [];

        foreach ($ownedLessons as $ownedLesson) {
            // skip current lesson, impossible to merge itself
            if ($ownedLesson->id == $lesson->id) {
                continue;
            }

            // skip lessons without exercises
            if ($ownedLesson->exercises()->count() == 0) {
                continue;
            }

            // skip lessons having subscribers other than the owner
            if ($ownedLesson->subscribedUsers()->count() > 1) {
                continue;
            }

            $lessons[] = [
                'id' => $ownedLesson->id,
                'name' => $ownedLesson->name,
            ];
        }

        $userLesson = $userLessonRepository->fetchUserLesson($lesson->id);

        return view(
            'lessons.merge',
            [
                'lessons' => $lessons,
            ] + $this->lessonViewData($userLesson)
        );
    }

    /**
     * @param Lesson $lesson
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function merge(Lesson $lesson, Request $request): RedirectResponse|Response
    {
        $this->validate(
            $request,
            [
                'toBeMerged' => 'required|array'
            ]
        );

        // user is not the owner of the lesson
        if ($lesson->owner_id != $this->user()->id) {
            return response('This action is unauthorized', Response::HTTP_FORBIDDEN);
        }

        foreach ($request->toBeMerged as $id) {
            $lessonToBeMerged = Lesson::query()->find($id);

            // lesson to be merged not found
            if (!$lessonToBeMerged instanceof Lesson) {
                return response('Not Found', Response::HTTP_NOT_FOUND);
            }

            // user is not the owner of the lesson to be merged
            if ($lessonToBeMerged->owner_id != $this->user()->id) {
                return response('This action is unauthorized', Response::HTTP_FORBIDDEN);
            }

            foreach ($lessonToBeMerged->exercises as $exercise) {
                $exercise->lesson_id = $lesson->id;
                $exercise->save();
            }
            $lessonToBeMerged->delete();

            event(new ExercisesMerged($lesson, $this->user()));
        }

        return redirect('/lessons/merge/' . $lesson->id);
    }
}
