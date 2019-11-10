<?php

namespace Tests\Models;

use App\Events\LessonSubscribed;
use App\Events\LessonUnsubscribed;
use Carbon\Carbon;

class LessonTest extends \TestCase
{
    // childLessons()

    /** @test */
    public function itShould_aggregateChildLessons()
    {
        $parentLesson = $this->createLesson();
        $childLesson = $this->createLesson();

        $parentLesson->childLessons()->attach($childLesson);

        $this->assertCount(1, $parentLesson->childLessons);

        $this->assertDatabaseHas('lesson_aggregate', [
            'parent_lesson_id' => $parentLesson->id,
            'child_lesson_id' => $childLesson->id,
        ]);
    }

    // parentLessons()

    /** @test */
    public function itShould_aggregateParentLessons()
    {
        $parentLesson = $this->createLesson();
        $childLesson = $this->createLesson();

        $childLesson->parentLessons()->attach($parentLesson);

        $this->assertCount(1, $childLesson->parentLessons);

        $this->assertDatabaseHas('lesson_aggregate', [
            'parent_lesson_id' => $parentLesson->id,
            'child_lesson_id' => $childLesson->id,
        ]);
    }

    // allExercises

    /** @test */
    public function itShould_fetchAllExercisesOfLesson()
    {
        $lesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertCount(2, $lesson->allExercises());
    }

    /** @test */
    public function itShould_fetchAllExercisesOfLesson_includeExercisesFromChildLesson()
    {
        $parentLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $parentLesson->id]);
        $childLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $childLesson->id]);

        $parentLesson->childLessons()->attach($childLesson);

        $this->assertCount(2, $parentLesson->allExercises());
    }

    /** @test */
    public function itShould_fetchAllExercisesOfLesson_includeExercisesFromChildLessons()
    {
        $parentLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $parentLesson->id]);
        $childLesson1 = $this->createLesson();
        $this->createExercise(['lesson_id' => $childLesson1->id]);
        $childLesson2 = $this->createLesson();
        $this->createExercise(['lesson_id' => $childLesson2->id]);

        $parentLesson->childLessons()->attach($childLesson1);
        $parentLesson->childLessons()->attach($childLesson2);

        $this->assertCount(3, $parentLesson->allExercises());
    }

    /** @test */
    public function itShould_fetchAllExercisesOfLesson_doNotIncludeExercisesFromOtherLessons()
    {
        $lesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExercise();

        $this->assertCount(2, $lesson->allExercises());
    }

    /** @test */
    public function itShould_fetchAllExercisesOfLesson_doNotIncludeExercisesFromOtherLessonsOfTheSameUser()
    {
        $user = $this->createUser();

        $lesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExercise(['lesson_id' => $lesson->id]);

        $anotherLesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $anotherLesson->id]);

        $this->assertCount(2, $lesson->allExercises());
    }

    /** @test */
    public function itShould_fetchAllExercisesOfLesson_doNotIncludeExercisesFromParentLessons()
    {
        $parentLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $parentLesson->id]);

        $childLesson = $this->createLesson();

        $childLesson->parentLessons()->attach($parentLesson);

        $this->assertCount(0, $childLesson->allExercises());
    }

    /** @test */
    public function itShould_fetchAllExercisesOfLesson_doNotIncludeExercisesFromGrandparentLessons()
    {
        $grandparentLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $grandparentLesson->id]);

        $parentLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $parentLesson->id]);

        $childLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $childLesson->id]);

        $grandparentLesson->childLessons()->attach($parentLesson);
        $parentLesson->childLessons()->attach($childLesson);

        $this->assertCount(2, $parentLesson->allExercises());
    }

    // subscribe()

    /** @test */
    public function itShould_excludeOwnerFromLessonSubscribers()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertEquals(1, $lesson->subscribedUsers()->count());
        $this->assertDatabaseHas('lesson_user', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'percent_of_good_answers' => 0,
        ]);

        $this->expectsEvents(LessonSubscribed::class);

        $lesson->subscribe($user = $this->createUser());
        $this->assertEquals(2, $lesson->subscribedUsers()->count());
        $this->assertDatabaseHas('lesson_user', [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->expectsEvents(LessonUnsubscribed::class);

        $lesson->unsubscribe($user);
        $this->assertEquals(1, $lesson->subscribedUsers()->count());
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

    // percentOfGoodAnswers()

    /** @test */
    public function itShould_fetchPercentOfGoodAnswers()
    {
        $lesson = $this->createPublicLesson();
        $user = $this->createUser();
        $lesson->subscribe($user);

        $this->assertEquals(0, $lesson->percentOfGoodAnswers($user->id));

        $user = $this->createUser();
        $lesson->subscribedUsers()->save($user, ['percent_of_good_answers' => 20]);

        $this->assertEquals(20, $lesson->percentOfGoodAnswers($user->id));
    }

    /** @test */
    public function itShould_notFetchPercentOfGoodAnswers_userDoesNotSubscribeLesson()
    {
        $lesson = $this->createPublicLesson();
        $user = $this->createUser();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User does not subscribe lesson: 1');

        $lesson->percentOfGoodAnswers($user->id);
    }

    // isBidirectional

    /** @test */
    public function itShould_fetchIsBidirectional_true()
    {
        $lesson = $this->createPublicLesson();
        $user = $this->createUser();
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['bidirectional' => true]);

        $this->assertEquals(true, $lesson->isBidirectional($user->id));
    }

    /** @test */
    public function itShould_fetchIsBidirectional_false()
    {
        $lesson = $this->createPublicLesson();
        $user = $this->createUser();
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['bidirectional' => false]);

        $this->assertEquals(false, $lesson->isBidirectional($user->id));
    }

    /** @test */
    public function itShould_fetchIsBidirectional_userDoesNotSubscribeLesson()
    {
        $lesson = $this->createPublicLesson();
        $user = $this->createUser();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User does not subscribe lesson: 1');

        $lesson->isBidirectional($user->id);
    }
}
