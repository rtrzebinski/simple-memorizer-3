<?php

namespace App\Http\Controllers\Web;

use App\Structures\UserLessonRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    /**
     * Show the application main page.
     *
     * @param UserLessonRepository $userLessonRepository
     * @return Response
     */
    public function index(UserLessonRepository $userLessonRepository)
    {
        if (!Auth::guest()) {
            redirect('/home');
        }

        return view('home', [
            'ownedLessons' => [],
            'subscribedLessons' => [],
            'availableLessons' => $userLessonRepository->fetchPublicUserLessons(),
            'userHasOwnedOrSubscribedLessons' => false,
        ]);
    }
}
