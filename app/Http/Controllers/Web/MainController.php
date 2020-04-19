<?php

namespace App\Http\Controllers\Web;

use App\Structures\UserLesson\AbstractUserLessonRepositoryInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MainController extends Controller
{
    /**
     * Show the application main page.
     *
     * @param AbstractUserLessonRepositoryInterface $userLessonRepository
     * @return Factory|Response|View
     */
    public function index(AbstractUserLessonRepositoryInterface $userLessonRepository)
    {
        if (!Auth::guest()) {
            redirect('/home');
        }

        return view('home', [
            'ownedLessons' => [],
            'subscribedLessons' => [],
            'availableLessons' => $userLessonRepository->fetchAvailableUserLessons(),
            'userHasOwnedOrSubscribedLessons' => false,
        ]);
    }
}
