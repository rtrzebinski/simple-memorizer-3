<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise\Exercise;
use App\Models\Lesson\Lesson;
use Illuminate\Http\RedirectResponse;
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
     * @param Lesson $lesson
     * @param StoreExerciseRequest $request
     * @return RedirectResponse
     */
    public function store(Lesson $lesson, StoreExerciseRequest $request) : RedirectResponse
    {
        $exercise = new Exercise($request->all());
        $exercise->lesson_id = $lesson->id;
        $exercise->save();

        return redirect('/lessons/' . $lesson->id);
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

    /**
     * @param Exercise $exercise
     * @param UpdateExerciseRequest $request
     * @return RedirectResponse
     */
    public function update(Exercise $exercise, UpdateExerciseRequest $request) : RedirectResponse
    {
        $exercise->update($request->all());

        return redirect('/lessons/' . $exercise->lesson_id);
    }

    /**
     * @param Exercise $exercise
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(Exercise $exercise) : RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $exercise);

        $exercise->delete();

        return redirect('/lessons/' . $exercise->lesson_id);
    }
}
