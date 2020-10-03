<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lesson::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'owner_id' => function () {
                return User::factory()->create()->id;
            },
            'name' => $this->faker->words(10, true),
            'visibility' => 'public',
            'exercises_count' => '0',
            'subscribers_count' => '0',
            'child_lessons_count' => '0',
        ];
    }
}
