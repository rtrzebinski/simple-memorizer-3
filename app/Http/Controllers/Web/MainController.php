<?php

namespace App\Http\Controllers\Web;

use App\Structures\UserLesson\GuestUserLessonRepositoryInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MainController extends Controller
{
    /**
     * Show the application main page.
     *
     * @param GuestUserLessonRepositoryInterface $userLessonRepository
     * @return Factory|Response|View
     */
    public function index(GuestUserLessonRepositoryInterface $userLessonRepository)
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
