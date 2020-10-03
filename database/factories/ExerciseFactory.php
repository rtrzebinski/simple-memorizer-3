<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Exercise::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'lesson_id' => function () {
                return Lesson::factory()->create()->id;
            },
            'question' => $this->faker->words(8, true),
            'answer' => $this->faker->words(2, true),
        ];
    }
}
