<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateLessonRequest;
use App\Http\Requests\DeleteLessonRequest;
use App\Http\Requests\SubscribeLessonRequest;
use App\Http\Requests\UnsubscribeLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson\LessonRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LessonController extends Controller
{
    /**
     * @param LessonRepositoryInterface $lessonRepository
     * @param CreateLessonRequest $request
     * @return JsonResponse
     */
    public function createLesson(LessonRepositoryInterface $lessonRepository, CreateLessonRequest $request)
    {
        $lesson = $lessonRepository->createLesson($request->all(), $this->user()->id);
        return $this->response($lesson, Response::HTTP_CREATED);
    }

    /**
     * @param LessonRepositoryInterface $lessonRepository
     * @param SubscribeLessonRequest $request
     */
    public function subscribeLesson(LessonRepositoryInterface $lessonRepository, SubscribeLessonRequest $request)
    {
        $lessonRepository->subscribeLesson($this->user()->id, $request->lesson_id);
    }

    /**
     * @param LessonRepositoryInterface $lessonRepository
     * @param UnsubscribeLessonRequest $request
     */
    public function unsubscribeLesson(LessonRepositoryInterface $lessonRepository, UnsubscribeLessonRequest $request)
    {
        $lessonRepository->unsubscribeLesson($this->user()->id, $request->lesson_id);
    }

    /**
     * @param LessonRepositoryInterface $lessonRepository
     * @param UpdateLessonRequest $request
     * @return JsonResponse
     */
    public function updateLesson(LessonRepositoryInterface $lessonRepository, UpdateLessonRequest $request)
    {
        $lesson = $lessonRepository->updateLesson($request->all(), $request->lesson_id);
        return $this->response($lesson);
    }

    /**
     * @param LessonRepositoryInterface $lessonRepository
     * @return JsonResponse
     */
    public function fetchOwnedLessons(LessonRepositoryInterface $lessonRepository)
    {
        $lessons = $lessonRepository->fetchOwnedLessons($this->user()->id);
        return $this->response($lessons);
    }

    /**
     * @param LessonRepositoryInterface $lessonRepository
     * @return JsonResponse
     */
    public function fetchSubscribedLessons(LessonRepositoryInterface $lessonRepository)
    {
        $lessons = $lessonRepository->fetchSubscribedLessons($this->user()->id);
        return $this->response($lessons);
    }

    /**
     * @param LessonRepositoryInterface $lessonRepository
     * @param DeleteLessonRequest $request
     */
    public function deleteLesson(LessonRepositoryInterface $lessonRepository, DeleteLessonRequest $request)
    {
        $lessonRepository->deleteLesson($request->lesson_id);
    }
}
