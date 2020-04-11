<?php

namespace Tests\Unit\Structures;

use App\Structures\GuestUserLessonRepository;
use App\Structures\UserLesson;
use Illuminate\Support\Collection;

class GuestUserLessonRepositoryTest extends \TestCase
{
    private GuestUserLessonRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new GuestUserLessonRepository();
    }

    // fetchPublicUserLessons

    /** @test */
    public function itShould_fetchPublicUserLessons()
    {
        $publicLesson = $this->createPublicLesson();
        $privateLesson = $this->createPrivateLesson();

        $result = $this->repository->fetchPublicUserLessons();

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
        $this->assertEquals($publicLesson->exercises_count, $result->exercises_count);
        $this->assertEquals($publicLesson->subscribers_count, $result->subscribers_count);
        $this->assertEquals($publicLesson->child_lessons_count, $result->child_lessons_count);
        $this->assertEquals(0, $result->is_subscriber);
        $this->assertEquals(0, $result->is_bidirectional);
        $this->assertEquals(0, $result->percent_of_good_answers);
    }

}
