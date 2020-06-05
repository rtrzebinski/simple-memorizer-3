<?php

namespace App\Http\Controllers\Web;

use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use App\Structures\UserLesson\UserLesson;
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

        $favouriteExercisesTotal = 0;

        /** @var UserLesson $userLesson */
        foreach ($data['ownedLessons'] as $userLesson) {
            if ($userLesson->is_favourite) {
                $favouriteExercisesTotal += $userLesson->exercises_count;
            }
        }

        /** @var UserLesson $userLesson */
        foreach ($data['subscribedLessons'] as $userLesson) {
            if ($userLesson->is_favourite) {
                $favouriteExercisesTotal += $userLesson->exercises_count;
            }
        }

        $data['userCanLearnFavouriteLessons'] = $favouriteExercisesTotal >= config('app.min_exercises_to_learn_lesson');

        return view('home', $data);
    }
}
