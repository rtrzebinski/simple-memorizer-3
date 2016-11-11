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
        $this->lesson('History: The greatest battles in British history', $exercises);

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
        $this->lesson('English: Common irregular verbs 1 (a-g)', $exercises);

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
        $this->lesson('English: Common irregular verbs 2 (h-z)', $exercises);

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

    /**
     * @param string $name
     * @param array $exercises
     * @return Lesson
     */
    private function lesson(string $name, array $exercises = []) : Lesson
    {
        $lesson = factory(Lesson::class)->create([
            'name' => $name,
        ]);

        foreach ($exercises as $k => $v) {
            factory(Exercise::class)->create([
                'lesson_id' => $lesson->id,
                'question' => $k,
                'answer' => $v,
            ]);
        }

        return $lesson;
    }
}
