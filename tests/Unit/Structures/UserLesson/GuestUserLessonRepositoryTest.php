<?php

namespace Tests\Unit\Structures\UserLesson;

use App\Structures\UserLesson\GuestUserLessonRepository;
use App\Structures\UserLesson\UserLesson;
use Illuminate\Support\Collection;

class GuestUserLessonRepositoryTest extends \TestCase
{
    // fetchUserLesson

    /** @test */
    public function itShould_fetchUserLesson_guest_notSubscribed()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
            'name' => uniqid(),
            'exercises_count' => 2,
            'subscribers_count' => 3,
            'child_lessons_count' => 4,
        ]);

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchUserLesson($lesson->id);

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
    public function itShould_fetchUserLesson_guest_subscribed()
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

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchUserLesson($lesson->id);

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
    public function itShould_fetchUserLesson_guest_bidirectional()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
        ]);
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['bidirectional' => true]);

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchUserLesson($lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals(null, $result->user_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals($lesson->visibility, $result->visibility);
        $this->assertEquals(0, $result->exercises_count);
        $this->assertEquals(1, $result->subscribers_count);
        $this->assertEquals(0, $result->child_lessons_count);
        $this->assertEquals(0, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_guest_notBidirectional()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
        ]);
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['bidirectional' => false]);

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchUserLesson($lesson->id);

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
    public function itShould_fetchUserLesson_guest_public()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createPublicLesson($user);

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchUserLesson($lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals(null, $result->user_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals('public', $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(0, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_guest_private()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createPrivateLesson($user);

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchUserLesson($lesson->id);

        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals(null, $result->user_id);
        $this->assertEquals($lesson->id, $result->lesson_id);
        $this->assertEquals($lesson->owner_id, $result->owner_id);
        $this->assertEquals($lesson->name, $result->name);
        $this->assertEquals('private', $result->visibility);
        $this->assertEquals($lesson->exercises_count, $result->exercises_count);
        $this->assertEquals($lesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($lesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(0, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserLesson_guest_userNotOwner()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createPublicLesson();
        $lesson->subscribe($user);

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchUserLesson($lesson->id);

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
    public function itShould_fetchUserLesson_guest_percentOfGoodAnswers()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson([
            'owner_id' => $user->id,
        ]);
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['percent_of_good_answers' => 50]);

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchUserLesson($lesson->id);

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

    // fetchPublicUserLessons

    /** @test */
    public function itShould_fetchAvailableUserLessons_guest_excludePrivateLessons()
    {
        $publicLesson = $this->createPublicLesson();

        $this->createExercisesRequiredToLearnLesson($publicLesson->id);

        // private lesson should be excluded
        $this->createPrivateLesson();

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchAvailableUserLessons();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);

        /** @var UserLesson $result */
        $result = $result[0];
        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals(null, $result->user_id);
        $this->assertEquals($publicLesson->id, $result->lesson_id);
        $this->assertEquals($publicLesson->owner_id, $result->owner_id);
        $this->assertEquals($publicLesson->name, $result->name);
        $this->assertEquals($publicLesson->visibility, $result->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $result->exercises_count);
        $this->assertEquals($publicLesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($publicLesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(0, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchAvailableUserLessons_guest_excludeLessonsWithNotEnoughExercisesRequiredToLearn()
    {
        $this->createPublicLesson();

        $repository = new GuestUserLessonRepository();
        $result = $repository->fetchAvailableUserLessons();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

}
