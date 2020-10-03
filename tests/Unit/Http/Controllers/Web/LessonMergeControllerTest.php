<?php

namespace Tests\Unit\Http\Controllers\Web;

use App\Events\ExercisesMerged;
use WebTestCase;

class LessonMergeControllerTest extends WebTestCase
{
    // index

    /** @test */
    public function itShould_indexLessonMerge()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($user);

        $ownedLesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $ownedLesson->id]);

        $this->call('GET', '/lessons/merge/'.$lesson->id);

        $this->assertResponseOk();

        $viewData = $this->responseView()->getData();

        $this->assertEquals($lesson->id, $viewData['userLesson']->lesson_id);
        $this->assertEquals([
            [
                'id' => $ownedLesson->id,
                'name' => $ownedLesson->name,
            ],
        ], $viewData['lessons']);
    }

    /** @test */
    public function itShould_indexLessonMerge_excludeLessonsWithoutExercises()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($user);

        // another lesson, without any exercises
        $this->createPublicLesson($user);

        $this->call('GET', '/lessons/merge/'.$lesson->id);

        $this->assertResponseOk();

        $viewData = $this->responseView()->getData();

        $this->assertEquals($lesson->id, $viewData['userLesson']->lesson_id);
        $this->assertEquals([], $viewData['lessons']);
    }

    /** @test */
    public function itShould_indexLessonMerge_excludeLessonsHavingSubscribedDifferentThanOwner()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($user);

        $ownedLesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $ownedLesson->id]);

        // subscriber other than owner
        $ownedLesson->subscribe($this->createUser());

        $this->call('GET', '/lessons/merge/'.$lesson->id);

        $this->assertResponseOk();

        $viewData = $this->responseView()->getData();

        $this->assertEquals($lesson->id, $viewData['userLesson']->lesson_id);
        $this->assertEquals([], $viewData['lessons']);
    }

    /** @test */
    public function itShould_notIndexLessonMerge_unauthorized()
    {
        $this->call('GET', '/lessons/merge/1');
        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notIndexLessonMerge_userDoesNotOwnLesson()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($this->createUser());

        $this->call('GET', '/lessons/merge/'.$lesson->id);

        $this->assertResponseForbidden();
    }

    // merge

    /** @test */
    public function itShould_mergeOneLessonIntoAnother()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $lesson->id]);

        $ownedLesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $ownedLesson->id]);

        $data = [
            'toBeMerged' => [
                $ownedLesson->id,
            ]
        ];

        $this->expectsEvents(ExercisesMerged::class);

        $this->call('POST', '/lessons/merge/'.$lesson->id, $data);

        $this->assertResponseRedirectedTo('/lessons/merge/'.$lesson->id);

        $this->assertEquals(2, $lesson->exercises()->count(), 'exercises not merged');
        $this->assertNull($ownedLesson->fresh(), 'merged lesson not deleted');
    }

    /** @test */
    public function itShould_mergeOneLessonIntoAnother_manyLessons()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $lesson->id]);

        $ownedLesson1 = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $ownedLesson1->id]);

        $ownedLesson2 = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $ownedLesson2->id]);

        $data = [
            'toBeMerged' => [
                $ownedLesson1->id,
                $ownedLesson2->id,
            ]
        ];

        $this->expectsEvents(ExercisesMerged::class);

        $this->call('POST', '/lessons/merge/'.$lesson->id, $data);

        $this->assertResponseRedirectedTo('/lessons/merge/'.$lesson->id);

        $this->assertEquals(3, $lesson->exercises()->count(), 'exercises not merged');
        $this->assertNull($ownedLesson1->fresh(), 'merged lesson not deleted');
        $this->assertNull($ownedLesson2->fresh(), 'merged lesson not deleted');
    }

    /** @test */
    public function itShould_notMergeOneLessonIntoAnother_unauthorized()
    {
        $this->call('POST', '/lessons/merge/1');

        $this->doesntExpectEvents(ExercisesMerged::class);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notMergeOneLessonIntoAnother_userDoesNotOwnTheLesson()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson();
        $this->createExercise(['lesson_id' => $lesson->id]);

        $ownedLesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $ownedLesson->id]);

        $data = [
            'toBeMerged' => [
                $ownedLesson->id,
            ]
        ];

        $this->doesntExpectEvents(ExercisesMerged::class);

        $this->call('POST', '/lessons/merge/'.$lesson->id, $data);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notMergeOneLessonIntoAnother_userDoesNotOwnLessonToBeMerged()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $lesson->id]);

        $ownedLesson = $this->createPublicLesson();
        $this->createExercise(['lesson_id' => $ownedLesson->id]);

        $data = [
            'toBeMerged' => [
                $ownedLesson->id,
            ]
        ];

        $this->doesntExpectEvents(ExercisesMerged::class);

        $this->call('POST', '/lessons/merge/'.$lesson->id, $data);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notMergeOneLessonIntoAnother_lessonNotFound()
    {
        $user = $this->createUser();
        $this->be($user);

        $this->doesntExpectEvents(ExercisesMerged::class);

        $this->call('POST', '/lessons/merge/-1');

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notMergeOneLessonIntoAnother_lessonToBeMergedNotFound()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $lesson->id]);

        $data = [
            'toBeMerged' => [
                -1,
            ]
        ];

        $this->doesntExpectEvents(ExercisesMerged::class);

        $this->call('POST', '/lessons/merge/'.$lesson->id, $data);

        $this->assertResponseNotFound();
    }

    /** @test */
    public function itShould_notMergeOneLessonIntoAnother_invalidInput()
    {
        $user = $this->createUser();
        $this->be($user);

        $lesson = $this->createPublicLesson($user);
        $this->createExercise(['lesson_id' => $lesson->id]);

        $this->doesntExpectEvents(ExercisesMerged::class);

        $this->call('POST', '/lessons/merge/'.$lesson->id);

        $this->assertResponseInvalidInput();
    }
}
