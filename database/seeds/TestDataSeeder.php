<?php

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

        /*
         * Public and private owned lessons
         */
        factory(Lesson::class)->create([
            'visibility' => 'public',
            'owner_id' => $user->id,
        ]);
        factory(Lesson::class)->create([
            'visibility' => 'private',
            'owner_id' => $user->id,
        ]);

        /*
         * Subscribed lesson
         */
        $subscribedLesson = factory(Lesson::class)->create([
            'visibility' => 'public',
        ]);
        $user->subscribedLessons()->save($subscribedLesson);

        /*
         * Available lessons
         */
        factory(Lesson::class, 6)->create([
            'visibility' => 'public',
        ]);

        /*
         * Not available lessons
         */
        factory(Lesson::class, 3)->create([
            'visibility' => 'private',
        ]);
    }
}
