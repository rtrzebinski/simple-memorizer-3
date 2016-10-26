<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'visibility' => 'required|in:public,private',
            'name' => 'required|string',
        ]);

        $lesson = new Lesson($request->all());
        $lesson->owner_id = $this->user()->id;
        $lesson->save();

        return redirect('/lessons/' . $lesson->id);
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
     * @param Request $request
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function update(Request $request, Lesson $lesson) : RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);

        $this->validate($request, [
            'visibility' => 'required|in:public,private',
            'name' => 'required|string',
        ]);

        $lesson->update($request->all());
        return redirect('/lessons/' . $lesson->id);
    }

    public function delete(Lesson $lesson)
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);
        $lesson->delete();
        return redirect('/home');
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
