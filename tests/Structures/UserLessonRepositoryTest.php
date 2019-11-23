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

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertSame('0', $result->percent_of_good_answers);
        $this->assertSame('0', $result->exercises_count);
        $this->assertSame('0', $result->child_lessons_count);
        $this->assertSame('0', $result->subscribers_count);
        $this->assertSame('0', $result->is_subscriber);
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

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(1, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
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

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_public()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createPublicLesson($user);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals('public', $result->visibility);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_private()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createPrivateLesson($user);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals('private', $result->visibility);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_userNotOwner()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createPublicLesson();
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['bidirectional' => true]);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(1, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_percentOfGoodAnswers()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
        ]);
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['percent_of_good_answers' => 50]);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(50, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_noExercises()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
        ]);
        $lesson->subscribe($user);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(0, $result->exercises_count);
    }

    /** @test */
    public function itShould_fetchUserLesson_exercisesCount()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
            'exercises_count' => 10,
        ]);
        $lesson->subscribe($user);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(10, $result->exercises_count);
    }

    /** @test */
    public function itShould_fetchUserLesson_subscribersCount()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
        ]);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(0, $result->subscribers_count);
        $this->assertEquals(0, $result->is_subscriber);

        // will make subscribers count 1, and is_subscriber true
        $lesson->subscribe($user);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(1, $result->subscribers_count);
        $this->assertEquals(true, $result->is_subscriber);
    }

    /** @test */
    public function itShould_fetchUserLesson_childLessonsCount()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
            'child_lessons_count' => 10,
        ]);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);

        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($user->id, $result->owner_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals(10, $result->child_lessons_count);
    }

}
