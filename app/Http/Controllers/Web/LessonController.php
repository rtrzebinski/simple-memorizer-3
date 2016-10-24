<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson\Lesson;

class LessonController extends Controller
{
    public function view(Lesson $lesson)
    {
        return view('lessons.view', compact('lesson'));
    }

    public function create()
    {

    }

    public function store()
    {

    }

    public function edit()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }
}
