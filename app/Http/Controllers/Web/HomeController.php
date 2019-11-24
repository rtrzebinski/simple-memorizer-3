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
        $data = [
            'ownedLessons' => $this->user()->ownedLessons()->with('exercises', 'subscribedUsers')->get(),
            'subscribedLessons' => $this->user()->subscribedLessons()->with('exercises', 'subscribedUsers')->get(),
            'availableLessons' => $this->user()->availableLessons(),
        ];

        $data['userHasOwnedOrSubscribedLessons'] = (bool)(count($data['ownedLessons']) + count($data['subscribedLessons']));

        return view('home', $data);
    }
}
