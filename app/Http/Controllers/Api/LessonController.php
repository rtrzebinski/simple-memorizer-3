<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PatchLessonRequest;
use App\Models\Lesson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeLesson(Request $request): JsonResponse
    {
        $this->validate($request, [
            'visibility' => 'required|in:'.implode(',', Lesson::VISIBILITIES),
            'name' => 'required|string',
        ]);

        $lesson = new Lesson($request->all());
        $lesson->owner_id = $this->user()->id;
        $lesson->save();

        // always subscribe owned lesson
        $lesson->subscribe($this->user());

        return $this->response($lesson);
    }

    /**
     * @param Lesson $lesson
     * @throws AuthorizationException
     */
    public function subscribeLesson(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'subscribe', $lesson);
        $lesson->subscribe($this->user());
    }

    /**
     * @param Lesson $lesson
     * @throws AuthorizationException
     */
    public function unsubscribeLesson(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'unsubscribe', $lesson);
        $lesson->unsubscribe($this->user());
    }

    /**
     * @param Lesson $lesson
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function fetchLesson(Lesson $lesson): JsonResponse
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);
        return $this->response($lesson);
    }

    /**
     * @return JsonResponse
     */
    public function fetchOwnedLessons(): JsonResponse
    {
        return $this->response($this->user()->ownedLessons);
    }

    /**
     * @return JsonResponse
     */
    public function fetchSubscribedLessons(): JsonResponse
    {
        return $this->response($this->user()->subscribedLessons);
    }

    /**
     * @param PatchLessonRequest $request
     * @param Lesson             $lesson
     * @return JsonResponse
     */
    public function updateLesson(PatchLessonRequest $request, Lesson $lesson): JsonResponse
    {
        $lesson->update($request->all());
        return $this->response($lesson);
    }

    /**
     * @param Lesson $lesson
     * @throws AuthorizationException
     */
    public function deleteLesson(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);
        $lesson->delete();
    }
}
