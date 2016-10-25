<?php

namespace App\Http\Controllers\Web;

use App\Models\Exercise\Exercise;
use App\Models\Lesson\Lesson;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    /**
     * @param Lesson $lesson
     * @return View
     */
    public function create(Lesson $lesson) : View
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);
        return view('exercises.create', compact('lesson'));
    }

    /**
     * @param Exercise $exercise
     * @return View
     */
    public function edit(Exercise $exercise) : View
    {
        $this->authorizeForUser($this->user(), 'modify', $exercise);
        return view('exercises.edit', [
            'exercise' => $exercise,
            'lesson' => $exercise->lesson,
        ]);
    }
}
