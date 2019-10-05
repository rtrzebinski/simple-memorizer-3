<?php

namespace App\Events;

use App\Models\Lesson;
use App\Models\User;

interface LessonEventInterface
{
    public function lesson(): Lesson;

    public function user(): User;
}
