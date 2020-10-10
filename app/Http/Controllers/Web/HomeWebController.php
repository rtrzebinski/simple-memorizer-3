<?php

namespace App\Http\Controllers\Web;

use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use App\Structures\UserLesson\UserLesson;
use Illuminate\Contracts\View\View;

class HomeWebController extends WebController
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

        $data['userHasOwnedOrSubscribedLessons'] = (bool)(count($data['ownedLessons']) + count(
                $data['subscribedLessons']
            ));

        $exercisesTotal = 0;
        $favouriteExercisesTotal = 0;

        /** @var UserLesson $userLesson */
        foreach ($data['ownedLessons'] as $userLesson) {
            $exercisesTotal += $userLesson->exercises_count;
            if ($userLesson->is_favourite) {
                $favouriteExercisesTotal += $userLesson->exercises_count;
            }
        }

        /** @var UserLesson $userLesson */
        foreach ($data['subscribedLessons'] as $userLesson) {
            $exercisesTotal += $userLesson->exercises_count;
            if ($userLesson->is_favourite) {
                $favouriteExercisesTotal += $userLesson->exercises_count;
            }
        }

        $data['userCanLearnAllLessons'] = $exercisesTotal >= config('app.min_exercises_to_learn_lesson');
        $data['userCanLearnFavouriteLessons'] = $favouriteExercisesTotal >= config('app.min_exercises_to_learn_lesson');

        return view('home', $data);
    }
}
