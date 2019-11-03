<?php

namespace Tests\Structures;

use App\Structures\UserLesson;
use App\Structures\UserLessonRepository;

class UserLessonRepositoryTest extends \TestCase
{
    /**
     * @var UserLessonRepository
     */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserLessonRepository();
    }

    /** @test */
    public function itShould_fetchUserLesson_notSubscribed()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
            'name' => uniqid(),
        ]);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals(0, $result->is_bidirectional);
    }

    /** @test */
    public function itShould_fetchUserLesson_bidirectional()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
        ]);
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['bidirectional' => true]);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(1, $result->is_bidirectional);
    }

    /** @test */
    public function itShould_fetchUserLesson_notBidirectional()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
        ]);
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['bidirectional' => false]);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(0, $result->is_bidirectional);
    }
}
