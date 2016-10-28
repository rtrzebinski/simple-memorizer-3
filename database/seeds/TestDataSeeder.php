<?php

use App\Models\Exercise\Exercise;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use App\Models\Lesson\Lesson;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = factory(User::class)->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
        ]);

        // private lessons
        factory(Lesson::class)->create([
            'name' => 'Private lesson with no exercises',
            'visibility' => 'private',
            'owner_id' => $user->id,
        ]);
        $lesson = factory(Lesson::class)->create([
            'name' => 'Private lesson with one exercise',
            'visibility' => 'private',
            'owner_id' => $user->id,
        ]);
        factory(Exercise::class)->create([
            'lesson_id' => $lesson->id,
        ]);
        $lesson = factory(Lesson::class)->create([
            'name' => 'Private lesson with two exercises',
            'visibility' => 'private',
            'owner_id' => $user->id,
        ]);
        factory(Exercise::class, 2)->create([
            'lesson_id' => $lesson->id,
        ]);

        // owned lesson
        $lesson = factory(Lesson::class)->create([
            'name' => 'Math: multiplication table 1-100',
            'owner_id' => $user->id,
        ]);
        for ($i = 1; $i <= 10; $i++) {
            for ($j = 1; $j <= 10; $j++) {
                factory(Exercise::class)->create([
                    'lesson_id' => $lesson->id,
                    'question' => $i . ' x ' . $j,
                    'answer' => $i * $j,
                ]);
            }
        }

        // subscribed lesson
        $lesson = factory(Lesson::class)->create([
            'name' => 'Math: multiplication table 100-400',
        ]);
        for ($i = 10; $i <= 20; $i++) {
            for ($j = 10; $j <= 20; $j++) {
                factory(Exercise::class)->create([
                    'lesson_id' => $lesson->id,
                    'question' => $i . ' x ' . $j,
                    'answer' => $i * $j,
                ]);
            }
        }
        $lesson->subscribers()->save($user);

        // other lessons

        $lesson = factory(Lesson::class)->create([
            'name' => 'Math: multiplication table 400-900',
        ]);
        for ($i = 20; $i <= 30; $i++) {
            for ($j = 20; $j <= 30; $j++) {
                factory(Exercise::class)->create([
                    'lesson_id' => $lesson->id,
                    'question' => $i . ' x ' . $j,
                    'answer' => $i * $j,
                ]);
            }
        }

        $lesson = factory(Lesson::class)->create([
            'name' => 'Math: adding integer numbers',
        ]);
        for ($i = 1; $i <= 100; $i++) {
            $a = rand(100, 10000);
            $b = rand(100, 10000);
            factory(Exercise::class)->create([
                'lesson_id' => $lesson->id,
                'question' => $a . ' + ' . $b,
                'answer' => $a + $b,
            ]);
        }

        $lesson = factory(Lesson::class)->create([
            'name' => 'Math: subtracting integer numbers',
        ]);
        for ($i = 1; $i <= 100; $i++) {
            $a = rand(100, 10000);
            $b = rand(100, 10000);
            factory(Exercise::class)->create([
                'lesson_id' => $lesson->id,
                'question' => $a . ' - ' . $b,
                'answer' => $a - $b,
            ]);
        }
    }
}
