<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\TestCommand;

class TestCommandTest extends \TestCase
{
    public function bracketsDataProvider(): array
    {
        return [
            [
                'in' => '(a+[b])',
                'out' => true,
            ],
            [
                'in' => '(a+(b)',
                'out' => false,
            ],
            [
                'in' => '(c{d)}',
                'out' => false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider bracketsDataProvider
     * @param string $in
     * @param bool $out
     */
    public function itShould_runTestCommand(string $in, bool $out)
    {
        $command = new TestCommand();

        $result = $command->handle($in);

        $this->assertSame($out, $result);
    }
}
