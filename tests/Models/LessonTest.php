<?php

namespace Tests\Models;

class LessonTest extends \TestCase
{
    // lessonAggregate()

    /** @test */
    public function itShould_aggregateLessons()
    {
        $parentLesson = $this->createLesson();
        $childLesson = $this->createLesson();

        $parentLesson->lessonAggregate()->attach($childLesson);

        $this->assertCount(1, $parentLesson->lessonAggregate);

        $this->assertDatabaseHas('lesson_aggregate', [
            'parent_lesson_id' => $parentLesson->id,
            'child_lesson_id' => $childLesson->id,
        ]);
    }

    /** @test */
    public function itShould_fetchAllExercisesOfLesson_includeExercisesFromChildLesson()
    {
        $parentLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $parentLesson->id]);
        $this->createExercise(['lesson_id' => $parentLesson->id]);
        $childLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExercise(['lesson_id' => $childLesson->id]);

        $parentLesson->lessonAggregate()->attach($childLesson);

        $this->assertCount(4, $parentLesson->all_exercises);
    }

    // subscribers()

    /** @test */
    public function itShould_excludeOwnerFromLessonSubscribers()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertEquals(0, $lesson->subscribersWithOwnerExcluded()->count());
        $this->assertEquals(1, $lesson->subscribers()->count());
        $this->assertDatabaseHas('lesson_user', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'percent_of_good_answers' => 0,
        ]);

        $lesson->subscribe($this->createUser());
        $this->assertEquals(2, $lesson->subscribers()->count());
        $this->assertEquals(1, $lesson->subscribersWithOwnerExcluded()->count());
    }

    // unsubscribe()

    /** @test */
    public function itShould_notAllowOwnerToUnsubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertDatabaseHas('lesson_user', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'percent_of_good_answers' => 0,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to unsubscribe owned lesson: 1');

        $lesson->unsubscribe($user);
    }

    // percentOfGoodAnswersOfUser()

    /** @test */
    public function itShould_fetchPercentOfGoodAnswersOfUser()
    {
        $lesson = $this->createPublicLesson();

        $user = $this->createUser();
        $lesson->subscribe($user);

        $this->assertEquals(0, $lesson->percentOfGoodAnswersOfUser($user->id));

        $user = $this->createUser();
        $lesson->subscribers()->save($user, ['percent_of_good_answers' => 20]);

        $this->assertEquals(20, $lesson->percentOfGoodAnswersOfUser($user->id));
    }

    /** @test */
    public function itShould_notFetchPercentOfGoodAnswersOfUser_userDoesNotSubscribeLesson()
    {
        $lesson = $this->createPublicLesson();

        $user = $this->createUser();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User does not subscribe lesson: 1');

        $lesson->percentOfGoodAnswersOfUser($user->id);
    }
}
