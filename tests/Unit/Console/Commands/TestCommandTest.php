<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\TestCommand;

class TestCommandTest extends \TestCase
{
    /** @test */
    public function itShould_runTestCommand()
    {
        $command = new TestCommand();

        $result = $command->handle();

        $this->assertSame(0, $result);
    }
}
