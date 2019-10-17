<?php

namespace Tests\Models;

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
    public function itShould_fetchAllExercisesOfLesson_includeExercisesFromChildLessons()
    {
        $parentLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $parentLesson->id]);
        $childLesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $childLesson->id]);

        $parentLesson->childLessons()->attach($childLesson);

        $this->assertCount(2, $parentLesson->allExercises());
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

    // exercisesForGivenUser()

    /** @test */
    public function itShould_fetchExercisesForGivenUser_includeExercisesBelowTheThreshold()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribedUsers()->save($user, ['threshold' => 100]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 50, // 50 is below 100 threshold - should be included
        ]);

        $this->assertCount(1, $lesson->exercisesForGivenUser($user->id));
    }

    /** @test */
    public function itShould_fetchExercisesForGivenUser_includeExercisesAtTheThreshold()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribedUsers()->save($user, ['threshold' => 100]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 100,
        ]);

        $this->assertCount(1, $lesson->exercisesForGivenUser($user->id));
    }

    /** @test */
    public function itShould_fetchExercisesForGivenUser_doNotIncludeExercisesAboveThreshold()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribedUsers()->save($user, ['threshold' => 50]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 100, // 100 is below 50 threshold - should not be included
        ]);

        $this->assertCount(0, $lesson->exercisesForGivenUser($user->id));
    }

    /** @test */
    public function itShould_fetchExercisesForGivenUser_includeExercisesBelowTheThreshold_ofChildLesson()
    {
        $user = $this->createUser();
        $parentLesson = $this->createLesson();
        $parentLesson->subscribedUsers()->save($user, ['threshold' => 100]);
        $childLesson = $this->createLesson();
        $parentLesson->childLessons()->attach($childLesson);

        $exercise = $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 50, // 50 is below 100 threshold - should be included
        ]);

        $this->assertCount(1, $parentLesson->exercisesForGivenUser($user->id));
    }

    /** @test */
    public function itShould_fetchExercisesForGivenUser_doNotIncludeExercisesAboveThreshold_ofChildLesson()
    {
        $user = $this->createUser();
        $parentLesson = $this->createLesson();
        $parentLesson->subscribedUsers()->save($user, ['threshold' => 50]);
        $childLesson = $this->createLesson();
        $parentLesson->childLessons()->attach($childLesson);

        $exercise = $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 100, // 100 is above 50 threshold - should not be included
        ]);

        $this->assertCount(0, $parentLesson->exercisesForGivenUser($user->id));
    }

    /** @test */
    public function itShould_fetchExercisesForGivenUser_includeExercisesWithoutAnyResults()
    {
        $lesson = $this->createLesson();
        $this->createExercise(['lesson_id' => $lesson->id]);
        $user = $this->createUser();

        $this->assertCount(1, $lesson->exercisesForGivenUser($user->id));
    }

    /** @test */
    public function itShould_fetchExercisesForGivenUser_excludeExercisesForAnotherUser()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $user1 = $this->createUser();
        $lesson->subscribedUsers()->save($user1, ['threshold' => 50]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user1->id,
            'percent_of_good_answers' => 40,
        ]);

        $user2 = $this->createUser();
        $lesson->subscribedUsers()->save($user2, ['threshold' => 50]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user2->id,
            'percent_of_good_answers' => 60,
        ]);

        $this->assertCount(1, $lesson->exercisesForGivenUser($user1->id));
        $this->assertCount(0, $lesson->exercisesForGivenUser($user2->id));
    }

    // subscribe()

    /** @test */
    public function itShould_excludeOwnerFromLessonSubscribers()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->assertEquals(0, $lesson->subscribedUsersWithOwnerExcluded()->count());
        $this->assertEquals(1, $lesson->subscribedUsers()->count());
        $this->assertDatabaseHas('lesson_user', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'percent_of_good_answers' => 0,
        ]);

        $lesson->subscribe($this->createUser());
        $this->assertEquals(2, $lesson->subscribedUsers()->count());
        $this->assertEquals(1, $lesson->subscribedUsersWithOwnerExcluded()->count());
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

    // threshold()

    /** @test */
    public function itShould_fetchThreshold()
    {
        $lesson = $this->createPublicLesson();
        $user = $this->createUser();
        $lesson->subscribe($user);
        $lesson->subscribedUsers()->updateExistingPivot($user->id, ['threshold' => 50]);

        $this->assertEquals(50, $lesson->threshold($user->id));
    }

    /** @test */
    public function itShould_fetchThreshold_userDoesNotSubscribeLesson()
    {
        $lesson = $this->createPublicLesson();
        $user = $this->createUser();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User does not subscribe lesson: 1');

        $lesson->threshold($user->id);
    }
}
