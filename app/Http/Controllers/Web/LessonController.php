<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * @param Lesson $lesson
     * @return mixed
     */
    public function view(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);
        return view('lessons.view', compact('lesson'));
    }

    /**
     * @param Lesson $lesson
     * @return View
     */
    public function learn(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);
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
        $this->authorizeForUser($this->user(), 'modify', $lesson);
        return view('lessons.edit', compact('lesson'));
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function subscribe(Lesson $lesson) : RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'subscribe', $lesson);
        $lesson->subscribe($this->user());
        return redirect('/home');
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function unsubscribe(Lesson $lesson) : RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'unsubscribe', $lesson);
        $lesson->unsubscribe($this->user());
        return redirect('/home');
    }
}