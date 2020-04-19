<?php

namespace App\Http\Controllers\Web;

use App\Models\Lesson;
use App\Structures\UserExercise\AbstractUserExerciseRepositoryInterface;
use App\Structures\UserLesson\AbstractUserLessonRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * @return View
     */
    public function create(): View
    {
        return view('lessons.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'visibility' => 'required|in:'.implode(',', Lesson::VISIBILITIES),
            'name' => 'required|string',
        ]);

        $lesson = new Lesson($request->all());
        $lesson->owner_id = $this->user()->id;
        $lesson->save();

        // always subscribe owned lesson
        $lesson->subscribe($this->user());

        return redirect('/lessons/'.$lesson->id);
    }

    /**
     * @param int                                   $lessonId
     * @param AbstractUserLessonRepositoryInterface $userLessonRepository
     * @return View
     * @throws AuthorizationException
     */
    public function view(int $lessonId, AbstractUserLessonRepositoryInterface $userLessonRepository): View
    {
        $userLesson = $userLessonRepository->fetchUserLesson($lessonId);

        $this->authorizeForUser($this->user(), 'access', $userLesson);

        return view('lessons.view', $this->lessonViewData($userLesson));
    }

    /**
     * @param Lesson                                  $lesson
     * @param AbstractUserLessonRepositoryInterface   $userLessonRepository
     * @param AbstractUserExerciseRepositoryInterface $userExerciseRepository
     * @return mixed
     * @throws AuthorizationException
     */
    public function exercises(Lesson $lesson, AbstractUserLessonRepositoryInterface $userLessonRepository, AbstractUserExerciseRepositoryInterface $userExerciseRepository): View
    {
        $this->authorizeForUser($this->user(), 'access', $lesson);

        $userExercises = $userExerciseRepository->fetchUserExercisesOfLesson($lesson->id);

        $userLesson = $userLessonRepository->fetchUserLesson($lesson->id);

        $data = [
                'canModifyLesson' => Gate::forUser($this->user())->allows('modify', $lesson),
                'userExercises' => $userExercises,
            ] + $this->lessonViewData($userLesson);

        return view('lessons.exercises', $data);
    }

    /**
     * @param int                                   $lessonId
     * @param AbstractUserLessonRepositoryInterface $userLessonRepository
     * @return View
     * @throws AuthorizationException
     */
    public function edit(int $lessonId, AbstractUserLessonRepositoryInterface $userLessonRepository): View
    {
        $userLesson = $userLessonRepository->fetchUserLesson($lessonId);

        $this->authorizeForUser($this->user(), 'modify', $userLesson);

        return view('lessons.edit', $this->lessonViewData($userLesson));
    }

    /**
     * @param Request $request
     * @param Lesson  $lesson
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function saveEdit(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);

        $this->validate($request, [
            'visibility' => 'required|in:'.implode(',', Lesson::VISIBILITIES),
            'name' => 'required|string',
        ]);

        $lesson->update($request->all());
        return redirect('/lessons/'.$lesson->id.'/edit');
    }

    /**
     * @param int                                   $lessonId
     * @param AbstractUserLessonRepositoryInterface $userLessonRepository
     * @return View|Response
     */
    public function settings(int $lessonId, AbstractUserLessonRepositoryInterface $userLessonRepository)
    {
        $userLesson = $userLessonRepository->fetchUserLesson($lessonId);

        // user does not subscribe lesson
        if (!$userLesson->is_subscriber) {
            return response('This action is unauthorized', Response::HTTP_FORBIDDEN);
        }

        return view('lessons.settings', $this->lessonViewData($userLesson));
    }

    /**
     * @param Request $request
     * @param Lesson  $lesson
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function saveSettings(Request $request, Lesson $lesson): RedirectResponse
    {
        if (!Gate::forUser($this->user())->denies('subscribe', $lesson)) {
            return redirect('/lessons/'.$lesson->id);
        }

        $this->validate($request, [
            'bidirectional' => 'required|boolean',
        ]);

        $pivot = $lesson->subscriberPivot($this->user()->id);
        $pivot->update([
            'bidirectional' => $request->bidirectional,
        ]);

        return redirect('/lessons/'.$lesson->id.'/settings');
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(Lesson $lesson): RedirectResponse
    {
        $this->authorizeForUser($this->user(), 'modify', $lesson);
        $lesson->delete();
        return redirect('/home');
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function subscribe(Lesson $lesson): RedirectResponse
    {
        $redirectUrl = '/home';

        // authenticated user
        if (Auth::check()) {
            if (Gate::forUser($this->user())->allows('subscribe', $lesson)) {
                $lesson->subscribe($this->user());
            }
            return redirect($redirectUrl);
        }

        // guest user
        session()->put('subscribe-lesson-id', $lesson->id);
        session()->put('subscribe-redirect-url', $redirectUrl);

        return redirect('/login');
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     * @throws \Exception
     */
    public function unsubscribe(Lesson $lesson): RedirectResponse
    {
        if (Gate::forUser($this->user())->allows('unsubscribe', $lesson)) {
            $lesson->unsubscribe($this->user());
        }
        return back();
    }

    /**
     * @param Lesson $lesson
     * @return RedirectResponse
     */
    public function subscribeAndLearn(Lesson $lesson): RedirectResponse
    {
        $redirectUrl = '/learn/lessons/'.$lesson->id;

        // authenticated user
        if (Auth::check()) {
            if (Gate::forUser($this->user())->allows('subscribe', $lesson)) {
                $lesson->subscribe($this->user());
            }
            return redirect($redirectUrl);
        }

        // guest user
        session()->put('subscribe-lesson-id', $lesson->id);
        session()->put('subscribe-redirect-url', $redirectUrl);

        return redirect('/login');
    }
}
