<?php

namespace Tests\Models\Lesson;

use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonRepository;
use App\Models\User\User;
use Illuminate\Support\Collection;
use TestCase;

class LessonRepositoryTest extends TestCase
{
    /**
     * @var LessonRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new LessonRepository();
    }

    public function testItShould_createLesson()
    {
        $user = $this->createUser();
        $attributes = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        /** @var Lesson $result */
        $result = $this->repository->createLesson($attributes, $user->id);

        $this->assertInstanceOf(Lesson::class, $result);
        $this->assertEquals($attributes['visibility'], $result->visibility);
        $this->assertEquals($attributes['name'], $result->name);
        $this->assertEquals($user->id, $result->owner_id);
    }

    public function testItShould_subscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->repository->subscribeLesson($user->id, $lesson->id);

        $this->assertEquals($user->id, $lesson->subscribers[0]->id);
    }

    public function testItShould_unsubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribers()->attach($user);

        $this->repository->unsubscribeLesson($user->id, $lesson->id);

        $this->assertCount(0, $lesson->subscribers);

    }

    public function testItShould_updateLesson()
    {
        $lesson = $this->createLesson();
        $attributes = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        /** @var Lesson $result */
        $result = $this->repository->updateLesson($attributes, $lesson->id);

        $this->assertInstanceOf(Lesson::class, $result);
        $this->assertEquals($attributes['visibility'], $result->visibility);
        $this->assertEquals($attributes['name'], $result->name);
    }

    public function testItShould_fetchOwnedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        /** @var Collection $result */
        $result = $this->repository->fetchOwnedLessons($user->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals($lesson->id, $result[0]->id);
    }

    public function testItShould_fetchSubscribedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $user->subscribedLessons()->save($lesson);

        /** @var Collection $result */
        $result = $this->repository->fetchSubscribedLessons($user->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals($lesson->id, $result[0]->id);
    }

    public function testItShould_deleteLesson()
    {
        $lesson = $this->createLesson();

        $this->repository->deleteLesson($lesson->id);

        $this->assertNull($lesson->fresh());
    }

    public function testItShould_authorizeSubscribeLesson_publicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertTrue($this->repository->authorizeSubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_authorizeSubscribeLesson_publicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertTrue($this->repository->authorizeSubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_authorizeSubscribeLesson_privateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->repository->authorizeSubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeSubscribeLesson_privateAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->repository->authorizeSubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeSubscribeLesson_userAlreadySubscribedPublicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->repository->authorizeSubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeSubscribeLesson_userAlreadySubscribedPublicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->repository->authorizeSubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeSubscribeLesson_userAlreadySubscribedPrivateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);
        $user->subscribedLessons()->save($lesson);

        $this->assertFalse($this->repository->authorizeSubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeSubscribeLesson_lessonDoesNotExist()
    {
        $this->assertFalse($this->repository->authorizeSubscribeLesson($this->createUser()->id, -1));
    }

    public function testItShould_authorizeUnsubscribeLesson_userSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->assertTrue($this->repository->authorizeUnsubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeUnsubscribeLesson_userDoesNotSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->assertFalse($this->repository->authorizeUnsubscribeLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeUnsubscribeLesson_lessonDoesNotExist()
    {
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeUnsubscribeLesson($user->id, -1));
    }

    public function testItShould_authorizeUpdateLesson_userIsLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->repository->authorizeUpdateLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeUpdateLesson_userIsNotLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->repository->authorizeUpdateLesson($user->id, $lesson->id));
    }

    public function testItShould_authorizeDeleteLesson_userIsLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->assertTrue($this->repository->authorizeDeleteLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeDeleteLesson_userIsNotLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->assertFalse($this->repository->authorizeDeleteLesson($user->id, $lesson->id));
    }

    protected function createPublicLesson(User $user = null) : Lesson
    {
        $attributes = ['visibility' => 'public'];

        if ($user) {
            $attributes['owner_id'] = $user->id;
        }

        return $this->createLesson($attributes);
    }

    protected function createPrivateLesson(User $user = null) : Lesson
    {
        $attributes = ['visibility' => 'private'];

        if ($user) {
            $attributes['owner_id'] = $user->id;
        }

        return $this->createLesson($attributes);
    }
}
