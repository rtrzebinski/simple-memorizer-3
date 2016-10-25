<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson\Lesson;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * @param Lesson $lesson
     * @return mixed
     */
    public function view(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'fetch', $lesson);
        return view('lessons.view', compact('lesson'));
    }

    /**
     * @param Lesson $lesson
     * @return View
     */
    public function learn(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'fetch', $lesson);
        return view('lessons.learn', compact('lesson'));
    }

    /**
     * @return View
     */
    public function create() : View
    {
        return view('lessons.create');
    }

    /**
     * @param Lesson $lesson
     * @return View
     */
    public function edit(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'update', $lesson);
        return view('lessons.edit', compact('lesson'));
    }
}
