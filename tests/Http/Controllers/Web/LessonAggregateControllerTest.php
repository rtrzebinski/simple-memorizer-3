<?php

namespace Tests\Http\Controllers\Web;

use App\Events\LessonAggregatesUpdated;

class LessonAggregateControllerTest extends BaseTestCase
{
    // index

    /** @test */
    public function itShould_indexLessonAggregate()
    {
        $user = $this->createUser();
        $this->be($user);

        $parentLesson = $this->createPrivateLesson($user);
        $childLesson = $this->createPrivateLesson($user);
        $parentLesson->childLessons()->attach($childLesson);
        $anotherLesson = $this->createPrivateLesson($user);

        $this->call('GET', '/lessons/aggregate/'.$parentLesson->id);
        $this->assertResponseOk();

        $viewData = $this->view()->getData();

        $this->assertEquals($parentLesson->id, $viewData['userLesson']->lesson_id);

        $this->assertEquals([
            [
                'id' => $childLesson->id,
                'name' => $childLesson->name,
                'is_aggregated' => true,
            ],
            [
                'id' => $anotherLesson->id,
                'name' => $anotherLesson->name,
                'is_aggregated' => false,
            ],
        ], $viewData['lessons']);
    }

    /** @test */
    public function itShould_notIndexLessonAggregate_unauthorized()
    {
        $parentLesson = $this->createLesson();

        $this->call('GET', '/lessons/aggregate/'.$parentLesson->id);

        $this->assertResponseUnauthorized();
    }

    // sync

    /** @test */
    public function itShould_syncLessonAggregate()
    {
        $user = $this->createUser();
        $this->be($user);

        $parentLesson = $this->createPrivateLesson($user);
        $childLesson = $this->createPrivateLesson($user);
        $parentLesson->childLessons()->attach($childLesson);
        $anotherLesson = $this->createPrivateLesson($user);

        $data = [
            'aggregates' => [$anotherLesson->id]
        ];

        $this->expectsEvents(LessonAggregatesUpdated::class);

        $this->call('POST', '/lessons/aggregate/'.$parentLesson->id, $data);
        $this->assertResponseRedirectedTo('/lessons/aggregate/'.$parentLesson->id);

        $parentLesson = $parentLesson->fresh();
        $this->assertCount(1, $parentLesson->childLessons);
        $this->assertEquals($anotherLesson->id, $parentLesson->childLessons[0]->id);
    }

    /** @test */
    public function itShould_syncLessonAggregate_clearAll()
    {
        $user = $this->createUser();
        $this->be($user);

        $parentLesson = $this->createPrivateLesson($user);
        $childLesson = $this->createPrivateLesson($user);
        $parentLesson->childLessons()->attach($childLesson);

        $this->expectsEvents(LessonAggregatesUpdated::class);

        $this->call('POST', '/lessons/aggregate/'.$parentLesson->id);
        $this->assertResponseRedirectedTo('/lessons/aggregate/'.$parentLesson->id);

        $parentLesson = $parentLesson->fresh();
        $this->assertCount(0, $parentLesson->childLessons);
    }

    /** @test */
    public function itShould_notSyncLessonAggregate_unauthorized()
    {
        $parentLesson = $this->createLesson();

        $this->call('POST', '/lessons/aggregate/'.$parentLesson->id);

        $this->assertResponseUnauthorized();
    }

    /** @test */
    public function itShould_notSyncLessonAggregate_invalidInput()
    {
        $user = $this->createUser();
        $this->be($user);

        $parentLesson = $this->createPrivateLesson($user);

        $data = [
            'aggregates' => uniqid(), // should be an array
        ];

        $this->call('POST', '/lessons/aggregate/'.$parentLesson->id, $data);

        $this->assertResponseInvalidInput();
    }
}
