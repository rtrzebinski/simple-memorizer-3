<?php

namespace App\Http\Controllers\Web;

use App\Structures\AuthenticatedUserLessonRepositoryInterface;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     *
     * @param AuthenticatedUserLessonRepositoryInterface $userLessonRepository
     * @return Response
     */
    public function index(AuthenticatedUserLessonRepositoryInterface $userLessonRepository)
    {
        $data = [
            'ownedLessons' => $userLessonRepository->fetchOwnedUserLessons($this->user()),
            'subscribedLessons' => $userLessonRepository->fetchSubscribedUserLessons($this->user()),
            'availableLessons' => $userLessonRepository->fetchAvailableUserLessons($this->user()),
        ];

        $data['userHasOwnedOrSubscribedLessons'] = (bool)(count($data['ownedLessons']) + count($data['subscribedLessons']));

        return view('home', $data);
    }
}
