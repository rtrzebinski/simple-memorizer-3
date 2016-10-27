<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson\Lesson;
use Illuminate\View\View;

class LearnController extends Controller
{
    /**
     * @param Lesson $lesson
     * @return View
     */
    public function learn(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);
        return view('lessons.learn', compact('lesson'));
    }
}
