<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createLesson(Request $request)
    {
        $this->validate($request, [
            'visibility' => 'required|in:public,private',
            'name' => 'required|string',
        ]);

        $lesson = new Lesson($request->all());
        $lesson->owner_id = $this->user()->id;
        $lesson->save();

        return $this->response($lesson);
    }

    /**
     * @return JsonResponse
     */
    public function fetchOwnedLessons()
    {
        return $this->response($this->user()->ownedLessons);
    }

    /**
     * @param Lesson $lesson
     */
    public function subscribeLesson(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'subscribe', $lesson);
        $lesson->subscribe($this->user());
    }

    /**
     * @param Lesson $lesson
     */
    public function unsubscribeLesson(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'unsubscribe', $lesson);
        $lesson->unsubscribe($this->user());
    }

    /**
     * @return JsonResponse
     */
    public function fetchSubscribedLessons()
    {
        return $this->response($this->user()->subscribedLessons);
    }

    /**
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function fetchLesson(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'fetch', $lesson);
        return $this->response($lesson);
    }

    /**
     * @param UpdateLessonRequest $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function updateLesson(UpdateLessonRequest $request, Lesson $lesson)
    {
        $lesson->update($request->all());
        return $this->response($lesson);
    }

    /**
     * @param Lesson $lesson
     */
    public function deleteLesson(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'delete', $lesson);
        $lesson->delete();
    }
}
