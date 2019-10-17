<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     *
     * @return Response
     */
    public function index()
    {
        return view('home', [
            'ownedLessons' => $this->user()->ownedLessons()->with('exercises', 'subscribedUsers')->get(),
            'subscribedLessons' => $this->user()->subscribedLessons()->with('exercises', 'subscribedUsers')->get(),
            'availableLessons' => $this->user()->availableLessons(),
            'userHasOwnedOrSubscribedLessons' => $this->user()->hasOwnedOrSubscribedLessons(),
        ]);
    }
}
