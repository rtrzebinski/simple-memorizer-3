<?php

namespace Tests\Unit\Structures;

use App\Structures\AuthenticatedUserExerciseRepository;
use App\Structures\UserExercise;
use Carbon\Carbon;

class AuthenticatedUserExerciseRepositoryTest extends \TestCase
{
    private AuthenticatedUserExerciseRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new AuthenticatedUserExerciseRepository();
    }

    // fetchUserExerciseOfExercise

    /** @test */
    public function itShould_fetchUserExerciseOfExercise_exerciseDoesNotExist()
    {
        $user = $this->createUser();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Exercise does not exist: 1');

        $this->repository->fetchUserExerciseOfExercise($user, $exerciseId = 1);
    }

    /** @test */
    public function itShould_fetchUserExerciseOfExercise_resultExists()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $exerciseResult = $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            "number_of_good_answers" => 2,
            "number_of_good_answers_today" => 1,
            "latest_good_answer" => Carbon::today()->addHours(1),
            "number_of_bad_answers" => 4,
            "number_of_bad_answers_today" => 3,
            "latest_bad_answer" => Carbon::today()->addHours(2),
            "percent_of_good_answers" => 5,
        ]);

        $result = $this->repository->fetchUserExerciseOfExercise($user, $exercise->id);

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($exercise->id, $result->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result->lesson_id);
        $this->assertEquals($exercise->question, $result->question);
        $this->assertEquals($exercise->answer, $result->answer);
        $this->assertEquals($exerciseResult->number_of_good_answers, $result->number_of_good_answers);
        $this->assertEquals($exerciseResult->number_of_good_answers_today, $result->number_of_good_answers_today);
        $this->assertEquals($exerciseResult->latest_good_answer, $result->latest_good_answer);
        $this->assertEquals($exerciseResult->number_of_bad_answers, $result->number_of_bad_answers);
        $this->assertEquals($exerciseResult->number_of_bad_answers_today, $result->number_of_bad_answers_today);
        $this->assertEquals($exerciseResult->latest_bad_answer, $result->latest_bad_answer);
        $this->assertEquals($exerciseResult->percent_of_good_answers, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExerciseOfExercise_resultDoesNotExist()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $result = $this->repository->fetchUserExerciseOfExercise($user, $exercise->id);

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($exercise->id, $result->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result->lesson_id);
        $this->assertEquals($exercise->question, $result->question);
        $this->assertEquals($exercise->answer, $result->answer);
        $this->assertSame(0, $result->number_of_good_answers);
        $this->assertSame(0, $result->number_of_good_answers_today);
        $this->assertSame(null, $result->latest_good_answer);
        $this->assertSame(0, $result->number_of_bad_answers);
        $this->assertSame(0, $result->number_of_bad_answers_today);
        $this->assertSame(null, $result->latest_bad_answer);
        $this->assertSame(0, $result->percent_of_good_answers);
    }

    // fetchUserExercisesWithPhrase

    /** @test */
    public function itShould_fetchUserExercisesWithPhrase_searchPhraseSameAsQuestion()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);

        $result = $this->repository->fetchUserExercisesWithPhrase($user, $phrase)[0];

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($exercise->id, $result->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result->lesson_id);
        $this->assertEquals($lesson->name, $result->lesson_name);
        $this->assertEquals($exercise->question, $result->question);
        $this->assertEquals($exercise->answer, $result->answer);
        $this->assertSame(0, $result->number_of_good_answers);
        $this->assertSame(0, $result->number_of_good_answers_today);
        $this->assertSame(null, $result->latest_good_answer);
        $this->assertSame(0, $result->number_of_bad_answers);
        $this->assertSame(0, $result->number_of_bad_answers_today);
        $this->assertSame(null, $result->latest_bad_answer);
        $this->assertSame(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesWithPhrase_searchPhraseSameAsQuestion_checkPercentOfGoodAnswers()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);
        $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'percent_of_good_answers' => 66,
        ]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $result = $this->repository->fetchUserExercisesWithPhrase($user, $phrase)[0];

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($exercise->id, $result->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result->lesson_id);
        $this->assertEquals($lesson->name, $result->lesson_name);
        $this->assertEquals($exercise->question, $result->question);
        $this->assertEquals($exercise->answer, $result->answer);
        $this->assertSame(0, $result->number_of_good_answers);
        $this->assertSame(0, $result->number_of_good_answers_today);
        $this->assertSame(null, $result->latest_good_answer);
        $this->assertSame(0, $result->number_of_bad_answers);
        $this->assertSame(0, $result->number_of_bad_answers_today);
        $this->assertSame(null, $result->latest_bad_answer);
        $this->assertSame(66, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesWithPhrase_searchPhraseContainedInQuestion()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $question = uniqid().$phrase.uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'question' => $question]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $result = $this->repository->fetchUserExercisesWithPhrase($user, $phrase)[0];

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($exercise->id, $result->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result->lesson_id);
        $this->assertEquals($lesson->name, $result->lesson_name);
        $this->assertEquals($exercise->question, $result->question);
        $this->assertEquals($exercise->answer, $result->answer);
        $this->assertSame(0, $result->number_of_good_answers);
        $this->assertSame(0, $result->number_of_good_answers_today);
        $this->assertSame(null, $result->latest_good_answer);
        $this->assertSame(0, $result->number_of_bad_answers);
        $this->assertSame(0, $result->number_of_bad_answers_today);
        $this->assertSame(null, $result->latest_bad_answer);
        $this->assertSame(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesWithPhrase_searchPhraseSameAsAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'answer' => $phrase]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $result = $this->repository->fetchUserExercisesWithPhrase($user, $phrase)[0];

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($exercise->id, $result->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result->lesson_id);
        $this->assertEquals($lesson->name, $result->lesson_name);
        $this->assertEquals($exercise->question, $result->question);
        $this->assertEquals($exercise->answer, $result->answer);
        $this->assertSame(0, $result->number_of_good_answers);
        $this->assertSame(0, $result->number_of_good_answers_today);
        $this->assertSame(null, $result->latest_good_answer);
        $this->assertSame(0, $result->number_of_bad_answers);
        $this->assertSame(0, $result->number_of_bad_answers_today);
        $this->assertSame(null, $result->latest_bad_answer);
        $this->assertSame(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesWithPhrase_searchPhraseContainedInAnswer()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson($user);

        $phrase = uniqid();

        $answer = uniqid().$phrase.uniqid();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'answer' => $answer]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $result = $this->repository->fetchUserExercisesWithPhrase($user, $phrase)[0];

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($exercise->id, $result->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result->lesson_id);
        $this->assertEquals($lesson->name, $result->lesson_name);
        $this->assertEquals($exercise->question, $result->question);
        $this->assertEquals($exercise->answer, $result->answer);
        $this->assertSame(0, $result->number_of_good_answers);
        $this->assertSame(0, $result->number_of_good_answers_today);
        $this->assertSame(null, $result->latest_good_answer);
        $this->assertSame(0, $result->number_of_bad_answers);
        $this->assertSame(0, $result->number_of_bad_answers_today);
        $this->assertSame(null, $result->latest_bad_answer);
        $this->assertSame(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesWithPhrase_findAllForEmptyPhrase()
    {
        $this->be($user = $this->createUser());

        $lesson = $this->createPrivateLesson($user);

        $phrase = '';

        $exercise = $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $result = $this->repository->fetchUserExercisesWithPhrase($user, $phrase)[0];

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($exercise->id, $result->exercise_id);
        $this->assertEquals($exercise->lesson_id, $result->lesson_id);
        $this->assertEquals($lesson->name, $result->lesson_name);
        $this->assertEquals($exercise->question, $result->question);
        $this->assertEquals($exercise->answer, $result->answer);
        $this->assertSame(0, $result->number_of_good_answers);
        $this->assertSame(0, $result->number_of_good_answers_today);
        $this->assertSame(null, $result->latest_good_answer);
        $this->assertSame(0, $result->number_of_bad_answers);
        $this->assertSame(0, $result->number_of_bad_answers_today);
        $this->assertSame(null, $result->latest_bad_answer);
        $this->assertSame(0, $result->percent_of_good_answers);
    }

    /** @test */
    public function itShould_fetchUserExercisesWithPhrase_noExercisesFound()
    {
        $this->be($user = $this->createUser());

        $phrase = uniqid();

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $result = $this->repository->fetchUserExercisesWithPhrase($user, $phrase);

        $this->assertEmpty($result);
    }

    /** @test */
    public function itShould_fetchUserExercisesWithPhrase_doNotSearchForExercisesOfOtherUsers()
    {
        $this->be($user = $this->createUser());

        $phrase = uniqid();

        // lesson of another user
        $lesson = $this->createPrivateLesson();
        $this->createExercise(['lesson_id' => $lesson->id, 'question' => $phrase]);

        $this->call('GET', '/exercises/search?phrase='.$phrase);

        $result = $this->repository->fetchUserExercisesWithPhrase($user, $phrase);

        $this->assertEmpty($result);
    }
}
