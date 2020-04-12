<?php

namespace App\Http\Controllers\Web;

use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     *
     * @param AuthenticatedUserLessonRepositoryInterface $userLessonRepository
     * @return View
     */
    public function index(AuthenticatedUserLessonRepositoryInterface $userLessonRepository)
    {
        $data = [
            'ownedLessons' => $userLessonRepository->fetchOwnedUserLessons(),
            'subscribedLessons' => $userLessonRepository->fetchSubscribedUserLessons(),
            'availableLessons' => $userLessonRepository->fetchAvailableUserLessons(),
        ];

        $data['userHasOwnedOrSubscribedLessons'] = (bool)(count($data['ownedLessons']) + count($data['subscribedLessons']));

        return view('home', $data);
    }
}
