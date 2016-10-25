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
        $ownedLessons = $this->user()->ownedLessons;
        $subscribedLessons = $this->user()->subscribedLessons;
        $availableLessons = $this->user()->availableLessons();
        $userHasOwnedOrSubscribedLessons = $this->user()->hasOwnedOrSubscribedLessons();

        return view('home',
            compact('ownedLessons', 'subscribedLessons', 'availableLessons', 'userHasOwnedOrSubscribedLessons'));
    }
}
