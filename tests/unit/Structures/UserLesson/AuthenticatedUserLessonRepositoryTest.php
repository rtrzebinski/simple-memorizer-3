<?php

namespace Tests\Unit\Structures\UserLesson;

use App\Structures\UserLesson\AuthenticatedUserLessonRepository;
use App\Structures\UserLesson\UserLesson;
use Illuminate\Support\Collection;

class AuthenticatedUserLessonRepositoryTest extends \TestCase
{
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

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchUserLesson($lesson->id);

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

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchUserLesson($lesson->id);

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

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchUserLesson($lesson->id);

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

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchUserLesson($lesson->id);

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

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchUserLesson($lesson->id);

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

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchUserLesson($lesson->id);

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

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchUserLesson($lesson->id);

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

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchUserLesson($lesson->id);

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

    // fetchOwnedUserLessons

    /** @test */
    public function itShould_fetchOwnedUserLessons()
    {
        $this->be($user = $this->createUser());
        $availableLesson = $this->createLesson();
        $subscribedLesson = $this->createLesson();
        $subscribedLesson->subscribe($user);
        $ownedPublicLesson = $this->createPublicLesson($user);
        $ownedPrivateLesson = $this->createPrivateLesson($user);

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchOwnedUserLessons();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);

        /** @var UserLesson $result1 */
        $result1 = $result[0];
        $this->assertInstanceOf(UserLesson::class, $result1);
        $this->assertEquals($user->id, $result1->user_id);
        $this->assertEquals($ownedPublicLesson->id, $result1->lesson_id);
        $this->assertEquals($ownedPublicLesson->owner_id, $result1->owner_id);
        $this->assertEquals($ownedPublicLesson->name, $result1->name);
        $this->assertEquals($ownedPublicLesson->visibility, $result1->visibility);
        $this->assertEquals($ownedPublicLesson->exercises_count, $result1->exercises_count);
        $this->assertEquals($ownedPublicLesson->subscribers_count, $result1->subscribers_count);
        $this->assertEquals($ownedPublicLesson->child_lessons_count, $result1->child_lessons_count);
        $this->assertEquals(1, $result1->is_subscriber);
        $this->assertEquals(0, $result1->is_bidirectional);
        $this->assertEquals(0, $result1->percent_of_good_answers);

        /** @var UserLesson $result2 */
        $result2 = $result[1];
        $this->assertInstanceOf(UserLesson::class, $result2);
        $this->assertEquals($user->id, $result2->user_id);
        $this->assertEquals($ownedPrivateLesson->id, $result2->lesson_id);
        $this->assertEquals($ownedPrivateLesson->owner_id, $result2->owner_id);
        $this->assertEquals($ownedPrivateLesson->name, $result2->name);
        $this->assertEquals($ownedPrivateLesson->visibility, $result2->visibility);
        $this->assertEquals($ownedPrivateLesson->exercises_count, $result2->exercises_count);
        $this->assertEquals($ownedPrivateLesson->subscribers_count, $result2->subscribers_count);
        $this->assertEquals($ownedPrivateLesson->child_lessons_count, $result2->child_lessons_count);
        $this->assertEquals(1, $result2->is_subscriber);
        $this->assertEquals(0, $result2->is_bidirectional);
        $this->assertEquals(0, $result2->percent_of_good_answers);
    }

    // fetchSubscribedUserLessons

    /** @test */
    public function itShould_fetchSubscribedUserLessons()
    {
        $this->be($user = $this->createUser());
        $availableLesson = $this->createLesson();
        $subscribedLesson = $this->createLesson();
        $subscribedLesson->subscribe($user);
        $ownedPublicLesson = $this->createPublicLesson($user);
        $ownedPrivateLesson = $this->createPrivateLesson($user);

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchSubscribedUserLessons();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);

        /** @var UserLesson $result2 $result1 */
        $result1 = $result[0];
        $this->assertInstanceOf(UserLesson::class, $result1);
        $this->assertEquals($user->id, $result1->user_id);
        $this->assertEquals($subscribedLesson->id, $result1->lesson_id);
        $this->assertEquals($subscribedLesson->owner_id, $result1->owner_id);
        $this->assertEquals($subscribedLesson->name, $result1->name);
        $this->assertEquals($subscribedLesson->visibility, $result1->visibility);
        $this->assertEquals($subscribedLesson->exercises_count, $result1->exercises_count);
        $this->assertEquals($subscribedLesson->subscribers_count, $result1->subscribers_count);
        $this->assertEquals($subscribedLesson->child_lessons_count, $result1->child_lessons_count);
        $this->assertEquals(1, $result1->is_subscriber);
        $this->assertEquals(0, $result1->is_bidirectional);
        $this->assertEquals(0, $result1->percent_of_good_answers);
    }

    // fetchAvailableUserLessons

    /** @test */
    public function itShould_fetchAvailableUserLessons_authenticated()
    {
        $this->be($user = $this->createUser());
        $availableLesson = $this->createLesson();
        $subscribedLesson = $this->createLesson();
        $subscribedLesson->subscribe($user);
        $ownedPublicLesson = $this->createPublicLesson($user);
        $ownedPrivateLesson = $this->createPrivateLesson($user);

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchAvailableUserLessons();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);

        /** @var UserLesson $result */
        $result = $result[0];
        $this->assertInstanceOf(UserLesson::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($availableLesson->id, $result->lesson_id);
        $this->assertEquals($availableLesson->owner_id, $result->owner_id);
        $this->assertEquals($availableLesson->name, $result->name);
        $this->assertEquals($availableLesson->visibility, $result->visibility);
        $this->assertEquals($availableLesson->exercises_count, $result->exercises_count);
        $this->assertEquals($availableLesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($availableLesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(0, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchAvailableUserLessons_authenticated_excludePrivateLessonsOfAnotherUsers()
    {
        $this->be($user = $this->createUser());

        $this->createLesson([
            'visibility' => 'private',
        ]);

        $repository = new AuthenticatedUserLessonRepository($user);
        $result = $repository->fetchAvailableUserLessons();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }
}
