<?php

namespace App\Models\Lesson;

use Illuminate\Support\Collection;

interface LessonRepositoryInterface
{
    public function createLesson(array $attributes, int $userId) : Lesson;

    public function subscribeLesson(int $userId, int $lessonId);

    public function unsubscribeLesson(int $userId, int $lessonId);

    public function updateLesson(array $attributes, int $lessonId) : Lesson;

    public function fetchOwnedLessons(int $userId) : Collection;

    public function fetchSubscribedLessons(int $userId) : Collection;

    public function deleteLesson(int $lessonId);

    public function authorizeSubscribeLesson(int $userId, int $lessonId) : bool;

    public function authorizeUnsubscribeLesson(int $userId, int $lessonId) : bool;

    public function authorizeUpdateLesson(int $userId, int $lessonId) : bool;

    public function authorizeDeleteLesson(int $userId, int $lessonId) : bool;
}
