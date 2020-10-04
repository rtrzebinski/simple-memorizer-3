<?php

namespace Tests\Unit\Services;

use App\Services\ArrayRandomizer;

class ArrayRandomizerTest extends \TestCase
{
    /** @test */
    public function itShouldFetchRandomElementOfGivenArray()
    {
        $arrayRandomizer = new ArrayRandomizer();

        $items = [uniqid(), uniqid()];

        $result = $arrayRandomizer->randomArrayElement($items);

        $this->assertTrue($result === $items[0] || $result === $items[1]);
    }
}
