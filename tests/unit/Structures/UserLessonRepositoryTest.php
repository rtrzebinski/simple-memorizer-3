<?php

namespace Tests\Unit\Structures;

use App\Structures\UserLesson;
use App\Structures\UserLessonRepository;

class UserLessonRepositoryTest extends \TestCase
{
    private UserLessonRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserLessonRepository();
    }

    // fetchUserLesson

    /** @test */
    public function itShould_fetchUserLesson_notSubscribed()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
            'name' => uniqid(),
            'exercises_count' => 2,
            'subscribers_count' => 3,
            'child_lessons_count' => 4,
        ]);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals($lesson->visibility, $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(0, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_guestUser()
    {
        $lesson = $this->createLesson([
            'name' => uniqid(),
            'exercises_count' => 2,
            'subscribers_count' => 3,
            'child_lessons_count' => 4,
        ]);

        $result = $this->repository->fetchUserLesson($user = null, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals(null, $result->user_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals($lesson->visibility, $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(0, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_subscribed()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
            'name' => uniqid(),
            'exercises_count' => 5,
            'subscribers_count' => 6,
            'child_lessons_count' => 7,
        ]);
        $lesson->subscribe($user);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals($lesson->visibility, $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(1, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
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
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals($lesson->visibility, $result->visibility);
        $this->assertEquals(0, $result->exercises_count);
        $this->assertEquals(1, $result->subscribers_count);
        $this->assertEquals(0, $result->child_lessons_count);
        $this->assertEquals(1, $result->is_subscriber);
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
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals($lesson->visibility, $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(1, $result->is_subscriber);
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
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals('public', $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(1, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
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
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals('private', $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(1, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_userNotOwner()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createPublicLesson();
        $lesson->subscribe($user);

        $result = $this->repository->fetchUserLesson($user, $lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals($lesson->visibility, $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(1, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
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
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals($lesson->visibility, $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(1, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(50, $result->percent_of_good_answers);
    }
}
