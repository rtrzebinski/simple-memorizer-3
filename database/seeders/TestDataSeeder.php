<?php

namespace Database\Seeders;

use App\Events\ExerciseCreated;
use App\Events\LessonAggregatesUpdated;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Lesson;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        // http://www.telegraph.co.uk/history/britain-at-war/9813502/The-20-greatest-battles-in-British-history.html
        $exercises = [
            '14 June 1645' => 'Naseby (First English Civil War)',
            '13 August 1704' => 'Blenheim (War of the Spanish Succession)',
            '16 April 1746' => 'Culloden (Jacobite Rebellion of 1745)',
            '23 June 1757' => 'Plassey (Seven Years War)',
            '13 September 1759' => 'Quebec (Seven Years War)',
            '19 April 1775' => 'Lexington (American War of Independence)',
            '22 July 1812' => 'Salamanca (Peninsular War)',
            '18 June 1815' => 'Waterloo (Napoleonic Wars)',
            '28 January 1846' => 'Aliwal (First Sikh War)',
            '25 October 1854' => 'Balaklava (Crimean War)',
            '22-23 January 1879' => 'Rorke’s Drift (Zulu War)',
            '25 April 1915 – 9 January 1916' => 'Gallipoli (World War One)',
            '1 July – 18 November 1916' => 'Somme (World War One)',
            '19 September – 31 October 1918' => 'Megiddo (World War One)',
            '23 October – 4 November 1942' => 'El Alamein (World War Two)',
            '6 June – 25 August 1944' => 'D-Day and the Battle for Normandy (World War Two)',
            '8 March – 3 July 1944' => 'Imphal/Kohima (World War Two)',
            '22-25 April 1951' => 'Imjin River (Korean War)',
            '28-29 May 1982' => 'Goose Green (Falklands War)',
            '17 July -12 September 2006' => 'Musa Qala (War in Afghanistan)',
        ];
        $this->lesson('History: The greatest battles in British history', $exercises, $bidirectional = true);

        // http://speakspeak.com/resources/vocabulary-elementary-pre-intermediate/70-common-irregular-verbs
        $exercises = [
            'become' => 'became, become',
            'begin' => 'began, begun',
            'break' => 'broke, broken',
            'bring' => 'brought, brought',
            'build' => 'built, built',
            'buy' => 'bought, bought',
            'catch' => 'caught, caught',
            'choose' => 'chose, chosen',
            'come' => 'came, come',
            'cost' => 'cost, cost',
            'cut' => 'cut, cut',
            'do' => 'did, done',
            'draw' => 'drew, drawn',
            'drink' => 'drank, drunk',
            'drive' => 'drove, driven',
            'eat' => 'ate, eaten',
            'fall' => 'fell, fallen',
            'feed' => 'fed, fed',
            'feel' => 'felt, felt',
            'fight' => 'fought, fought',
            'find' => 'found, found',
            'fly' => 'flew, flown',
            'forget' => 'forgot, forgotten',
            'forgive' => 'forgave, forgiven',
            'get' => 'got, got',
            'give' => 'gave, given',
            'go' => 'went, gone',
            'grow' => 'grew, grown',
        ];
        $this->lesson('English: Common irregular verbs 1 (a-g)', $exercises, $bidirectional = true);

        // http://speakspeak.com/resources/vocabulary-elementary-pre-intermediate/70-common-irregular-verbs
        $exercises = [
            'have' => 'had, had',
            'hear' => 'heard, heard',
            'hide' => 'hid, hidden',
            'hit' => 'hit, hit',
            'hold' => 'held, held',
            'know' => 'knew, known',
            'learn' => 'learned, learnt/learned',
            'leave' => 'left, left',
            'lend' => 'lent, lent',
            'lose' => 'lost, lost',
            'make' => 'made, made',
            'mean' => 'meant, meant',
            'meet' => 'met, met',
            'pay' => 'paid, paid',
            'put' => 'put, put',
            'read' => 'read, read',
            'ride' => 'rode, ridden',
            'rise' => 'rose, risen',
            'run' => 'ran, run',
            'say' => 'said, said',
            'see' => 'saw, seen',
            'sell' => 'sold, sold',
            'send' => 'sent, sent',
            'set' => 'set, set',
            'show' => 'showed, shown',
            'sing' => 'sang, sung',
            'sit' => 'sat, sat',
            'sleep' => 'slept, slept',
            'speak' => 'spoke, spoken',
            'spend' => 'spent, spent',
            'stand' => 'stood, stood',
            'steal' => 'stole, stolen',
            'swim' => 'swam, swum',
            'take' => 'took, taken',
            'teach' => 'taught, taught',
            'tell' => 'told, told',
            'think' => 'thought, thought',
            'throw' => 'threw, thrown',
            'understand' => 'understood, understood',
            'wear' => 'wore, worn',
            'win' => 'won, won',
            'write' => 'wrote, written',
        ];
        $this->lesson('English: Common irregular verbs 2 (h-z)', $exercises, $bidirectional = true);

        $user = User::factory()->create(
            [
                'email' => 'admin@example.com',
                'password' => '$2y$12$/WfobkrcnlmQRIAAWcyw5OU6c9cj13SxGJNYtLSiTVhn8c0jQr1Au',
                // password: admin@example.com
            ]
        );

        // private lessons
        $lesson = Lesson::factory()->create(
            [
                'name' => 'Private lesson with no exercises',
                'visibility' => 'private',
                'owner_id' => $user->id,
            ]
        );
        $lesson->subscribe($user);

        $lesson = Lesson::factory()->create(
            [
                'name' => 'Private lesson with one exercise',
                'visibility' => 'private',
                'owner_id' => $user->id,
            ]
        );
        $lesson->subscribe($user);
        Exercise::factory()->create(
            [
                'lesson_id' => $lesson->id,
            ]
        );
        event(new ExerciseCreated($lesson, $lesson->owner));

        $lesson = Lesson::factory()->create(
            [
                'name' => 'Private lesson with two exercises',
                'visibility' => 'private',
                'owner_id' => $user->id,
            ]
        );
        $lesson->subscribe($user);
        Exercise::factory()
            ->count(2)
            ->create(
                [
                    'lesson_id' => $lesson->id,
                ]
            );
        event(new ExerciseCreated($lesson, $lesson->owner));

        $lesson = Lesson::factory()->create(
            [
                'name' => 'Private lesson with three exercises',
                'visibility' => 'private',
                'owner_id' => $user->id,
            ]
        );
        $lesson->subscribe($user);
        Exercise::factory()
            ->count(3)
            ->create(
                [
                    'lesson_id' => $lesson->id,
                ]
            );
        event(new ExerciseCreated($lesson, $lesson->owner));

        // owned lessons
        $ownedMathLesson1 = Lesson::factory()->create(
            [
                'name' => 'Math: multiplication table 1-100',
                'owner_id' => $user->id,
            ]
        );
        $ownedMathLesson1->subscribe($user);
        for ($i = 1; $i <= 10; $i++) {
            for ($j = 1; $j <= 10; $j++) {
                Exercise::factory()->create(
                    [
                        'lesson_id' => $ownedMathLesson1->id,
                        'question' => $i . ' x ' . $j,
                        'answer' => $i * $j,
                    ]
                );
            }
        }
        event(new ExerciseCreated($ownedMathLesson1, $lesson->owner));

        $ownedMathLesson2 = Lesson::factory()->create(
            [
                'name' => 'Math: multiplication table 100-400',
                'owner_id' => $user->id,
            ]
        );
        $ownedMathLesson2->subscribe($user);
        for ($i = 10; $i <= 20; $i++) {
            for ($j = 10; $j <= 20; $j++) {
                Exercise::factory()->create(
                    [
                        'lesson_id' => $ownedMathLesson2->id,
                        'question' => $i . ' x ' . $j,
                        'answer' => $i * $j,
                    ]
                );
            }
        }
        event(new ExerciseCreated($ownedMathLesson2, $ownedMathLesson2->owner));

        // owned aggregate lesson
        /** @var Lesson $lesson */
        $lesson = Lesson::factory()->create(
            [
                'name' => 'All my math lessons aggregated',
                'owner_id' => $user->id,
            ]
        );
        $lesson->subscribe($user);
        $lesson->childLessons()->attach($ownedMathLesson1);
        $lesson->childLessons()->attach($ownedMathLesson2);
        event(new LessonAggregatesUpdated($lesson, $ownedMathLesson2->owner));

        // subscribed lesson
        $lesson = Lesson::factory()->create(
            [
                'name' => 'Math: multiplication table 400-900',
            ]
        );
        $lesson->subscribe($lesson->owner);
        for ($i = 20; $i <= 30; $i++) {
            for ($j = 20; $j <= 30; $j++) {
                Exercise::factory()->create(
                    [
                        'lesson_id' => $lesson->id,
                        'question' => $i . ' x ' . $j,
                        'answer' => $i * $j,
                    ]
                );
                event(new ExerciseCreated($lesson, $lesson->owner));
            }
        }
        $lesson->subscribe($user);

        // other lessons
        $lesson = Lesson::factory()->create(
            [
                'name' => 'Math: adding integer numbers',
            ]
        );
        for ($i = 1; $i <= 100; $i++) {
            $a = rand(100, 10000);
            $b = rand(100, 10000);
            Exercise::factory()->create(
                [
                    'lesson_id' => $lesson->id,
                    'question' => $a . ' + ' . $b,
                    'answer' => $a + $b,
                ]
            );
            event(new ExerciseCreated($lesson, $lesson->owner));
        }
        $lesson->subscribe($lesson->owner);

        $lesson = Lesson::factory()->create(
            [
                'name' => 'Math: subtracting integer numbers',
            ]
        );
        for ($i = 1; $i <= 100; $i++) {
            $a = rand(100, 10000);
            $b = rand(100, 10000);
            Exercise::factory()->create(
                [
                    'lesson_id' => $lesson->id,
                    'question' => $a . ' - ' . $b,
                    'answer' => $a - $b,
                ]
            );
        }
        event(new ExerciseCreated($lesson, $lesson->owner));
        $lesson->subscribe($lesson->owner);

        $lesson = Lesson::factory()->create(
            [
                'name' => 'Private lesson of another user',
                'visibility' => 'private',
            ]
        );
        for ($i = 1; $i <= 5; $i++) {
            $a = rand(10, 100);
            $b = rand(10, 100);
            Exercise::factory()->create(
                [
                    'lesson_id' => $lesson->id,
                    'question' => $a . ' - ' . $b,
                    'answer' => $a - $b,
                ]
            );
        }
        event(new ExerciseCreated($lesson, $lesson->owner));
        $lesson->subscribe($lesson->owner);

        $lesson = Lesson::factory()->create(
            [
                'name' => 'Just one exercise lesson',
                'visibility' => 'public',
            ]
        );
        Exercise::factory()->create(
            [
                'lesson_id' => $lesson->id,
                'question' => $a . ' - ' . $b,
                'answer' => $a - $b,
            ]
        );
        event(new ExerciseCreated($lesson, $lesson->owner));
        $lesson->subscribe($lesson->owner);
    }

    /**
     * @param string $name
     * @param array $exercises
     * @param bool $bidirectional
     * @return Lesson
     */
    private function lesson(string $name, array $exercises, bool $bidirectional): Lesson
    {
        /** @var Lesson $lesson */
        $lesson = Lesson::factory()->create(
            [
                'name' => $name,
            ]
        );

        $lesson->subscribe($lesson->owner);

        $lesson->subscribedUsers()
            ->where('lesson_user.user_id', '=', $lesson->owner->id)
            ->update(['bidirectional' => $bidirectional]);

        foreach ($exercises as $k => $v) {
            Exercise::factory()->create(
                [
                    'lesson_id' => $lesson->id,
                    'question' => $k,
                    'answer' => $v,
                ]
            );
        }
        event(new ExerciseCreated($lesson, $lesson->owner));

        return $lesson;
    }
}
