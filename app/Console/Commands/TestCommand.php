<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * A sandbox command to play with code.
 *
 * Run with: $ php artisan test --filter TestCommandTest
 *
 * Class TestCommand
 * @package App\Console\Commands
 */
class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param string $in
     * @return bool
     */
    public function handle(string $in): bool
    {
        $stack = [];

        $brackets = [
            '(' => ')',
            '[' => ']',
            '{' => '}',
        ];

        foreach (str_split($in) as $c) {
            // opening exists
            if (array_key_exists($c, $brackets)) {
                $stack[] = $c;
            }

            // closing exists
            if (in_array($c, $brackets)) {
                $key = array_search($c, $brackets);
                if ($key != array_pop($stack)) {
                    return false;
                }
            }
        }

        return empty($stack);
    }
}
