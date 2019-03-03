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

        $this->seeInDatabase('lesson_aggregate', [
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
}
