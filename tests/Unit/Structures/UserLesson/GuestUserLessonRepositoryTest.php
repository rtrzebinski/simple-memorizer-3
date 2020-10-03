<?php

namespace Tests\Unit\Structures\UserLesson;

use App\Structures\UserLesson\GuestUserLessonRepository;
use App\Structures\UserLesson\UserLesson;
use Illuminate\Support\Collection;

class GuestUserLessonRepositoryTest extends \TestCase
{
    // fetchUserLesson

    /** @test */
    public function itShould_fetchUserLesson_guest()
    {
        $user = $this->createUser(['id' => 5]);
        $lesson = $this->createLesson(
            [
                'owner_id' => $user->id,
                'name' => uniqid(),
                'exercises_count' => 2,
                'subscribers_count' => 3,
                'child_lessons_count' => 4,
            ]
        );

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
    }

    // fetchAvailableUserLessons

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
