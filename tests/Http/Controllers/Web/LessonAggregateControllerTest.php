<?php

namespace Tests\Http\Controllers\Web;

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
        $parentLesson->lessonAggregate()->attach($childLesson);
        $anotherLesson = $this->createPrivateLesson($user);

        $this->call('GET', '/lessons/aggregate/'.$parentLesson->id);
        $this->assertResponseOk();

        $viewData = $this->view()->getData();

        $this->assertEquals($parentLesson->id, $viewData['lesson']->id);

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
        $parentLesson->lessonAggregate()->attach($childLesson);
        $anotherLesson = $this->createPrivateLesson($user);

        $data = [
            'aggregates' => [$anotherLesson->id]
        ];

        $this->call('POST', '/lessons/aggregate/'.$parentLesson->id, $data);
        $this->assertResponseRedirectedTo('/lessons/aggregate/'.$parentLesson->id);

        $parentLesson = $parentLesson->fresh();
        $this->assertCount(1, $parentLesson->lessonAggregate);
        $this->assertEquals($anotherLesson->id, $parentLesson->lessonAggregate[0]->id);
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

        $this->call('POST', '/lessons/aggregate/'.$parentLesson->id);

        $this->assertResponseInvalidInput();
    }
}
