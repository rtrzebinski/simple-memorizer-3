<?php

namespace Tests\Integration\Http\Controllers\Api;

use ApiTestCase;

class LearnLessonControllerTest extends ApiTestCase
{
    // fetchRandomExerciseOfLesson

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random', $data = [], $user);

        $this->assertResponseOk();
        $response = $this->response->decodeResponseJson();
        $this->assertIsInt($response['exercise_id']);
        $this->assertIsInt($response['lesson_id']);
        $this->assertIsString($response['lesson_name']);
        $this->assertTrue(strlen($response['lesson_name']) > 0);
        $this->assertIsString($response['question']);
        $this->assertTrue(strlen($response['question']) > 0);
        $this->assertIsString($response['answer']);
        $this->assertTrue(strlen($response['answer']) > 0);
        $this->assertSame(0, $response['number_of_good_answers']);
        $this->assertSame(0, $response['number_of_good_answers_today']);
        $this->assertNull($response['latest_good_answer']);
        $this->assertSame(0, $response['number_of_bad_answers']);
        $this->assertSame(0, $response['number_of_bad_answers_today']);
        $this->assertNull($response['latest_bad_answer']);
        $this->assertSame(0, $response['percent_of_good_answers']);
    }

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random',
            ['previous_exercise_id' => $previous->id], $user);

        $this->assertResponseOk();
        $response = $this->response->decodeResponseJson();

        $this->assertIsInt($response['exercise_id']);
        $this->assertIsInt($response['lesson_id']);
        $this->assertIsString($response['lesson_name']);
        $this->assertTrue(strlen($response['lesson_name']) > 0);
        $this->assertIsString($response['question']);
        $this->assertTrue(strlen($response['question']) > 0);
        $this->assertIsString($response['answer']);
        $this->assertTrue(strlen($response['answer']) > 0);
        $this->assertSame(0, $response['number_of_good_answers']);
        $this->assertSame(0, $response['number_of_good_answers_today']);
        $this->assertNull($response['latest_good_answer']);
        $this->assertSame(0, $response['number_of_bad_answers']);
        $this->assertSame(0, $response['number_of_bad_answers_today']);
        $this->assertNull($response['latest_bad_answer']);
        $this->assertSame(0, $response['percent_of_good_answers']);
        $this->assertTrue($response['exercise_id'] != $previous->id);
    }

    /** @test */
    public function itShould_fetchRandomExerciseOfLesson_lessonIsBidirectional()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $this->updateBidirectional($lesson, $user->id, $bidirectional = true);

        $this->callApi('GET', '/lessons/'.$lesson->id.'/exercises/random', $data = [], $user);

        $response = $this->response->decodeResponseJson();
        $this->assertResponseOk();
        $this->assertIsInt($response['exercise_id']);
        $this->assertIsInt($response['lesson_id']);
        $this->assertIsString($response['lesson_name']);
        $this->assertTrue(strlen($response['lesson_name']) > 0);
        $this->assertIsString($response['question']);
        $this->assertTrue(strlen($response['question']) > 0);
        $this->assertIsString($response['answer']);
        $this->assertTrue(strlen($response['answer']) > 0);
        $this->assertSame(0, $response['number_of_good_answers']);
        $this->assertSame(0, $response['number_of_good_answers_today']);
        $this->assertNull($response['latest_good_answer']);
        $this->assertSame(0, $response['number_of_bad_answers']);
        $this->assertSame(0, $response['number_of_bad_answers_today']);
        $this->assertNull($response['latest_bad_answer']);
        $this->assertSame(0, $response['percent_of_good_answers']);
    }
}
