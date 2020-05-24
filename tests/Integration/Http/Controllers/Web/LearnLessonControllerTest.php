<?php

namespace Tests\Integration\Http\Controllers\Web;

use App\Structures\UserExercise\UserExercise;
use App\Structures\UserLesson\UserLesson;
use WebTestCase;

class LearnLessonControllerTest extends WebTestCase
{
    // learnLesson

    /** @test */
    public function itShould_showLessonLearnPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('GET', '/learn/lessons/'.$lesson->id);

        $this->assertResponseOk();

        /** @var UserLesson $userLesson */
        $userLesson = $this->view()->getData()['userLesson'];
        $this->assertEquals($user->id, $userLesson->user_id);
        $this->assertEquals($lesson->id, $userLesson->lesson_id);
        $this->assertEquals($lesson->owner_id, $userLesson->owner_id);
        $this->assertEquals($lesson->name, $userLesson->name);
        $this->assertEquals(false, $userLesson->is_bidirectional);
        $this->assertEquals('public', $userLesson->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $userLesson->exercises_count);
        $this->assertEquals(0, $userLesson->percent_of_good_answers);
        $this->assertEquals(1, $userLesson->subscribers_count);
        $this->assertEquals(0, $userLesson->child_lessons_count);
        $this->assertEquals(true, $userLesson->is_subscriber);

        /** @var UserExercise $userExercise */
        $userExercise = $this->view()->getData()['userExercise'];
        $this->assertIsInt($userExercise->exercise_id);
        $this->assertIsInt($userExercise->lesson_id);
        $this->assertIsString($userExercise->lesson_name);
        $this->assertTrue(strlen($userExercise->lesson_name) > 0);
        $this->assertIsString($userExercise->question);
        $this->assertTrue(strlen($userExercise->question) > 0);
        $this->assertIsString($userExercise->answer);
        $this->assertTrue(strlen($userExercise->answer) > 0);
        $this->assertSame(0, $userExercise->number_of_good_answers);
        $this->assertSame(0, $userExercise->number_of_good_answers_today);
        $this->assertNull($userExercise->latest_good_answer);
        $this->assertSame(0, $userExercise->number_of_bad_answers);
        $this->assertSame(0, $userExercise->number_of_bad_answers_today);
        $this->assertNull($userExercise->latest_bad_answer);
        $this->assertSame(0, $userExercise->percent_of_good_answers);
    }

    /** @test */
    public function itShould_showLessonLearnPage_withPreviousExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createExercise()->lesson;
        $lesson->subscribe($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $previous = $lesson->exercises[0];

        $this->call('GET', '/learn/lessons/'.$lesson->id.'?previous_exercise_id='.$previous->id);

        $this->assertResponseOk();

        /** @var UserLesson $userLesson */
        $userLesson = $this->view()->getData()['userLesson'];
        $this->assertEquals($user->id, $userLesson->user_id);
        $this->assertEquals($lesson->id, $userLesson->lesson_id);
        $this->assertEquals($lesson->owner_id, $userLesson->owner_id);
        $this->assertEquals($lesson->name, $userLesson->name);
        $this->assertEquals(false, $userLesson->is_bidirectional);
        $this->assertEquals('public', $userLesson->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $userLesson->exercises_count);
        $this->assertEquals(0, $userLesson->percent_of_good_answers);
        $this->assertEquals(1, $userLesson->subscribers_count);
        $this->assertEquals(0, $userLesson->child_lessons_count);
        $this->assertEquals(true, $userLesson->is_subscriber);

        /** @var UserExercise $userExercise */
        $userExercise = $this->view()->getData()['userExercise'];
        $this->assertIsInt($userExercise->exercise_id);
        $this->assertIsInt($userExercise->lesson_id);
        $this->assertIsString($userExercise->lesson_name);
        $this->assertTrue(strlen($userExercise->lesson_name) > 0);
        $this->assertIsString($userExercise->question);
        $this->assertTrue(strlen($userExercise->question) > 0);
        $this->assertIsString($userExercise->answer);
        $this->assertTrue(strlen($userExercise->answer) > 0);
        $this->assertSame(0, $userExercise->number_of_good_answers);
        $this->assertSame(0, $userExercise->number_of_good_answers_today);
        $this->assertNull($userExercise->latest_good_answer);
        $this->assertSame(0, $userExercise->number_of_bad_answers);
        $this->assertSame(0, $userExercise->number_of_bad_answers_today);
        $this->assertNull($userExercise->latest_bad_answer);
        $this->assertSame(0, $userExercise->percent_of_good_answers);
        $this->assertTrue($userExercise->exercise_id != $previous->id);
    }

    /** @test */
    public function itShould_showLessonLearnPage_withRequestedExercise()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $requested = $lesson->exercises[0];

        $this->call('GET', '/learn/lessons/'.$lesson->id.'?requested_exercise_id='.$requested->id);

        /** @var UserLesson $userLesson */
        $userLesson = $this->view()->getData()['userLesson'];
        $this->assertEquals($user->id, $userLesson->user_id);
        $this->assertEquals($lesson->id, $userLesson->lesson_id);
        $this->assertEquals($lesson->owner_id, $userLesson->owner_id);
        $this->assertEquals($lesson->name, $userLesson->name);
        $this->assertEquals(false, $userLesson->is_bidirectional);
        $this->assertEquals('private', $userLesson->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $userLesson->exercises_count);
        $this->assertEquals(0, $userLesson->percent_of_good_answers);
        $this->assertEquals(1, $userLesson->subscribers_count);
        $this->assertEquals(0, $userLesson->child_lessons_count);
        $this->assertEquals(true, $userLesson->is_subscriber);

        /** @var UserExercise $userExercise */
        $userExercise = $this->view()->getData()['userExercise'];
        $this->assertIsInt($userExercise->exercise_id);
        $this->assertIsInt($userExercise->lesson_id);
        $this->assertNull($userExercise->lesson_name);
        $this->assertIsString($userExercise->question);
        $this->assertTrue(strlen($userExercise->question) > 0);
        $this->assertIsString($userExercise->answer);
        $this->assertTrue(strlen($userExercise->answer) > 0);
        $this->assertSame(0, $userExercise->number_of_good_answers);
        $this->assertSame(0, $userExercise->number_of_good_answers_today);
        $this->assertNull($userExercise->latest_good_answer);
        $this->assertSame(0, $userExercise->number_of_bad_answers);
        $this->assertSame(0, $userExercise->number_of_bad_answers_today);
        $this->assertNull($userExercise->latest_bad_answer);
        $this->assertSame(0, $userExercise->percent_of_good_answers);
        $this->assertTrue($userExercise->exercise_id == $requested->id);
    }

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson_lessonIsBidirectional()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);
        $this->updateBidirectional($lesson, $user->id, $bidirectional = true);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->call('GET', '/learn/lessons/'.$lesson->id);

        $this->assertResponseOk();

        /** @var UserLesson $userLesson */
        $userLesson = $this->view()->getData()['userLesson'];
        $this->assertEquals($user->id, $userLesson->user_id);
        $this->assertEquals($lesson->id, $userLesson->lesson_id);
        $this->assertEquals($lesson->owner_id, $userLesson->owner_id);
        $this->assertEquals($lesson->name, $userLesson->name);
        $this->assertEquals($bidirectional, $userLesson->is_bidirectional);
        $this->assertEquals('public', $userLesson->visibility);
        $this->assertEquals(config('app.min_exercises_to_learn_lesson'), $userLesson->exercises_count);
        $this->assertEquals(0, $userLesson->percent_of_good_answers);
        $this->assertEquals(1, $userLesson->subscribers_count);
        $this->assertEquals(0, $userLesson->child_lessons_count);
        $this->assertEquals(true, $userLesson->is_subscriber);

        /** @var UserExercise $userExercise */
        $userExercise = $this->view()->getData()['userExercise'];
        $this->assertIsInt($userExercise->exercise_id);
        $this->assertIsInt($userExercise->lesson_id);
        $this->assertIsString($userExercise->lesson_name);
        $this->assertTrue(strlen($userExercise->lesson_name) > 0);
        $this->assertIsString($userExercise->question);
        $this->assertTrue(strlen($userExercise->question) > 0);
        $this->assertIsString($userExercise->answer);
        $this->assertTrue(strlen($userExercise->answer) > 0);
        $this->assertSame(0, $userExercise->number_of_good_answers);
        $this->assertSame(0, $userExercise->number_of_good_answers_today);
        $this->assertNull($userExercise->latest_good_answer);
        $this->assertSame(0, $userExercise->number_of_bad_answers);
        $this->assertSame(0, $userExercise->number_of_bad_answers_today);
        $this->assertNull($userExercise->latest_bad_answer);
        $this->assertSame(0, $userExercise->percent_of_good_answers);
    }
}
