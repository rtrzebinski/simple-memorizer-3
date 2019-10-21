<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    /**
     * Show the application main page.
     *
     * @return Response
     */
    public function index()
    {
        if (!Auth::guest()) {
            redirect('/home');
        }

        return view('home', [
            'ownedLessons' => [],
            'subscribedLessons' => [],
            'availableLessons' => Lesson::whereVisibility('public')->get(),
            'userHasOwnedOrSubscribedLessons' => false,
        ]);
    }
}
