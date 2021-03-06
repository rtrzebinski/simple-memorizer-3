<?php

namespace Tests\Unit\Structures\UserExercise;

use App\Structures\UserExercise\UserExercise;
use App\Structures\UserExercise\GuestUserExerciseRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GuestUserExerciseRepositoryTest extends \TestCase
{
    // fetchUserExercisesOfLesson

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_noExercises()
    {
        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson(1);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_excludeExercisesOfAnotherLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $this->createExercise(['lesson_id' => $lesson->id]);

        // exercise of another lesson
        $this->createExercise(['lesson_id' => $this->createLesson()->id]);

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($lesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_oneExerciseWithResult()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $exerciseResult = $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                "number_of_good_answers" => 2,
                "number_of_good_answers_today" => 1,
                "latest_good_answer" => Carbon::today()->addHours(1),
                "number_of_bad_answers" => 4,
                "number_of_bad_answers_today" => 3,
                "latest_bad_answer" => Carbon::today()->addHours(2),
                "percent_of_good_answers" => 5,
            ]
        );

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($lesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(UserExercise::class, $result[0]);
        $this->assertEquals($exercise->id, $result[0]->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result[0]->lesson_id);
        $this->assertEquals($exercise->question, $result[0]->question);
        $this->assertEquals($exercise->answer, $result[0]->answer);
        $this->assertEquals(null, $result[0]->number_of_good_answers);
        $this->assertEquals(null, $result[0]->number_of_good_answers_today);
        $this->assertEquals(null, $result[0]->latest_good_answer);
        $this->assertEquals(null, $result[0]->number_of_bad_answers);
        $this->assertEquals(null, $result[0]->number_of_bad_answers_today);
        $this->assertEquals(null, $result[0]->latest_bad_answer);
        $this->assertEquals(null, $result[0]->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_twoExercisesWithResults()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $exerciseResult1 = $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise1->id,
                "number_of_good_answers" => 2,
                "number_of_good_answers_today" => 1,
                "latest_good_answer" => Carbon::today()->addHours(1),
                "number_of_bad_answers" => 4,
                "number_of_bad_answers_today" => 3,
                "latest_bad_answer" => Carbon::today()->addHours(2),
                "percent_of_good_answers" => 5,
            ]
        );

        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $exerciseResult2 = $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise2->id,
                "number_of_good_answers" => 4,
                "number_of_good_answers_today" => 2,
                "latest_good_answer" => Carbon::today()->addHours(2),
                "number_of_bad_answers" => 8,
                "number_of_bad_answers_today" => 6,
                "latest_bad_answer" => Carbon::today()->addHours(4),
                "percent_of_good_answers" => 10,
            ]
        );

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($lesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);

        $this->assertInstanceOf(UserExercise::class, $result[0]);
        $this->assertEquals($exercise1->id, $result[0]->exercise_id);
        $this->assertEquals($exercise1->lesson_id, $result[0]->lesson_id);
        $this->assertEquals($exercise1->question, $result[0]->question);
        $this->assertEquals($exercise1->answer, $result[0]->answer);
        $this->assertEquals(null, $result[0]->number_of_good_answers);
        $this->assertEquals(null, $result[0]->number_of_good_answers_today);
        $this->assertEquals(null, $result[0]->latest_good_answer);
        $this->assertEquals(null, $result[0]->number_of_bad_answers);
        $this->assertEquals(null, $result[0]->number_of_bad_answers_today);
        $this->assertEquals(null, $result[0]->latest_bad_answer);
        $this->assertEquals(null, $result[0]->percent_of_good_answers);

        $this->assertInstanceOf(UserExercise::class, $result[1]);
        $this->assertEquals($exercise2->id, $result[1]->exercise_id);
        $this->assertEquals($exercise2->lesson_id, $result[1]->lesson_id);
        $this->assertEquals($exercise2->question, $result[1]->question);
        $this->assertEquals($exercise2->answer, $result[1]->answer);
        $this->assertEquals(null, $result[1]->number_of_good_answers);
        $this->assertEquals(null, $result[1]->number_of_good_answers_today);
        $this->assertEquals(null, $result[1]->latest_good_answer);
        $this->assertEquals(null, $result[1]->number_of_bad_answers);
        $this->assertEquals(null, $result[1]->number_of_bad_answers_today);
        $this->assertEquals(null, $result[1]->latest_bad_answer);
        $this->assertEquals(null, $result[1]->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_oneExerciseWithoutResult()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($lesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(UserExercise::class, $result[0]);
        $this->assertEquals($exercise->id, $result[0]->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result[0]->lesson_id);
        $this->assertEquals($exercise->question, $result[0]->question);
        $this->assertEquals($exercise->answer, $result[0]->answer);
        $this->assertSame(null, $result[0]->number_of_good_answers);
        $this->assertSame(null, $result[0]->number_of_good_answers_today);
        $this->assertSame(null, $result[0]->latest_good_answer);
        $this->assertSame(null, $result[0]->number_of_bad_answers);
        $this->assertSame(null, $result[0]->number_of_bad_answers_today);
        $this->assertSame(null, $result[0]->latest_bad_answer);
        $this->assertSame(null, $result[0]->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_twoExercisesOneWithResultOneWithout()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $exerciseResult1 = $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise1->id,
                "number_of_good_answers" => 2,
                "number_of_good_answers_today" => 1,
                "latest_good_answer" => Carbon::today()->addHours(1),
                "number_of_bad_answers" => 4,
                "number_of_bad_answers_today" => 3,
                "latest_bad_answer" => Carbon::today()->addHours(2),
                "percent_of_good_answers" => 5,
            ]
        );

        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($lesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);

        $this->assertInstanceOf(UserExercise::class, $result[0]);
        $this->assertEquals($exercise1->id, $result[0]->exercise_id);
        $this->assertEquals($exercise1->lesson_id, $result[0]->lesson_id);
        $this->assertEquals($exercise1->question, $result[0]->question);
        $this->assertEquals($exercise1->answer, $result[0]->answer);
        $this->assertEquals(null, $result[0]->number_of_good_answers);
        $this->assertEquals(null, $result[0]->number_of_good_answers_today);
        $this->assertEquals(null, $result[0]->latest_good_answer);
        $this->assertEquals(null, $result[0]->number_of_bad_answers);
        $this->assertEquals(null, $result[0]->number_of_bad_answers_today);
        $this->assertEquals(null, $result[0]->latest_bad_answer);
        $this->assertEquals(null, $result[0]->percent_of_good_answers);

        $this->assertInstanceOf(UserExercise::class, $result[1]);
        $this->assertEquals($exercise2->id, $result[1]->exercise_id);
        $this->assertEquals($exercise2->lesson_id, $result[1]->lesson_id);
        $this->assertEquals($exercise2->question, $result[1]->question);
        $this->assertEquals($exercise2->answer, $result[1]->answer);
        $this->assertSame(null, $result[1]->number_of_good_answers);
        $this->assertSame(null, $result[1]->number_of_good_answers_today);
        $this->assertSame(null, $result[1]->latest_good_answer);
        $this->assertSame(null, $result[1]->number_of_bad_answers);
        $this->assertSame(null, $result[1]->number_of_bad_answers_today);
        $this->assertSame(null, $result[1]->latest_bad_answer);
        $this->assertSame(null, $result[1]->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_twoExercisesBothWithoutResult()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($lesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);

        $this->assertInstanceOf(UserExercise::class, $result[0]);
        $this->assertEquals($exercise1->id, $result[0]->exercise_id);
        $this->assertEquals($exercise1->lesson_id, $result[0]->lesson_id);
        $this->assertEquals($exercise1->question, $result[0]->question);
        $this->assertEquals($exercise1->answer, $result[0]->answer);
        $this->assertSame(null, $result[0]->number_of_good_answers);
        $this->assertSame(null, $result[0]->number_of_good_answers_today);
        $this->assertSame(null, $result[0]->latest_good_answer);
        $this->assertSame(null, $result[0]->number_of_bad_answers);
        $this->assertSame(null, $result[0]->number_of_bad_answers_today);
        $this->assertSame(null, $result[0]->latest_bad_answer);
        $this->assertSame(null, $result[0]->percent_of_good_answers);

        $this->assertInstanceOf(UserExercise::class, $result[1]);
        $this->assertEquals($exercise2->id, $result[1]->exercise_id);
        $this->assertEquals($exercise2->lesson_id, $result[1]->lesson_id);
        $this->assertEquals($exercise2->question, $result[1]->question);
        $this->assertEquals($exercise2->answer, $result[1]->answer);
        $this->assertSame(null, $result[1]->number_of_good_answers);
        $this->assertSame(null, $result[1]->number_of_good_answers_today);
        $this->assertSame(null, $result[1]->latest_good_answer);
        $this->assertSame(null, $result[1]->number_of_bad_answers);
        $this->assertSame(null, $result[1]->number_of_bad_answers_today);
        $this->assertSame(null, $result[1]->latest_bad_answer);
        $this->assertSame(null, $result[1]->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_oneExerciseWithResultOfAnotherUser()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                "number_of_good_answers" => 2,
                "number_of_good_answers_today" => 1,
                "latest_good_answer" => Carbon::today()->addHours(1),
                "number_of_bad_answers" => 4,
                "number_of_bad_answers_today" => 3,
                "latest_bad_answer" => Carbon::today()->addHours(2),
                "percent_of_good_answers" => 5,
            ]
        );

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($lesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(UserExercise::class, $result[0]);
        $this->assertEquals($exercise->id, $result[0]->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result[0]->lesson_id);
        $this->assertEquals($exercise->question, $result[0]->question);
        $this->assertEquals($exercise->answer, $result[0]->answer);
        $this->assertSame(null, $result[0]->number_of_good_answers);
        $this->assertSame(null, $result[0]->number_of_good_answers_today);
        $this->assertSame(null, $result[0]->latest_good_answer);
        $this->assertSame(null, $result[0]->number_of_bad_answers);
        $this->assertSame(null, $result[0]->number_of_bad_answers_today);
        $this->assertSame(null, $result[0]->latest_bad_answer);
        $this->assertSame(null, $result[0]->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_includeExercisesOfParentAndChildLessons()
    {
        $user = $this->createUser();
        $parentLesson = $this->createLesson(['owner_id' => $user->id]);
        $childLesson = $this->createLesson(['owner_id' => $user->id]);
        $parentLesson->childLessons()->save($childLesson);

        $exercise = $this->createExercise(['lesson_id' => $parentLesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
            ]
        );

        $exercise = $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
            ]
        );

        // some unrelated exercise (should be excluded)
        $this->createExercise();

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($parentLesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_includeExerciseOfChildLessonOnly()
    {
        $user = $this->createUser();
        $parentLesson = $this->createLesson(['owner_id' => $user->id]);
        $childLesson = $this->createLesson(['owner_id' => $user->id]);
        $parentLesson->childLessons()->save($childLesson);

        $exercise = $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
            ]
        );

        // some unrelated exercise (should be excluded)
        $this->createExercise();

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($parentLesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_excludeExercisesOfParentLesson()
    {
        $user = $this->createUser();
        $parentLesson = $this->createLesson(['owner_id' => $user->id]);
        $childLesson = $this->createLesson(['owner_id' => $user->id]);
        $parentLesson->childLessons()->save($childLesson);

        $this->createExercise(['lesson_id' => $parentLesson->id]);

        // some unrelated exercise (should be excluded)
        $this->createExercise();

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($childLesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    /** @test */
    public function itShould_fetchUserExercisesOfLesson_guest_excludeExercisesOfGrandchildLesson()
    {
        $user = $this->createUser();
        $parentLesson = $this->createLesson(['owner_id' => $user->id]);
        $childLesson = $this->createLesson(['owner_id' => $user->id]);
        $grandchildLesson = $this->createLesson(['owner_id' => $user->id]);
        $parentLesson->childLessons()->save($childLesson);
        $childLesson->childLessons()->save($grandchildLesson);

        $exercise = $this->createExercise(['lesson_id' => $grandchildLesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
            ]
        );

        // some unrelated exercise (should be excluded)
        $this->createExercise();

        $repository = new GuestUserExerciseRepository();
        $result = $repository->fetchUserExercisesOfLesson($parentLesson->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }
}
