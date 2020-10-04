<?php

namespace App\Services;

/**
 * Extract getting a random element of an array, so it can me mocked for testing.
 *
 * Class RandomizationService
 * @package App\Services
 */
class ArrayRandomizer
{
    /**
     * @param array $items
     * @return mixed
     */
    public function randomArrayElement(array $items)
    {
        return $items[array_rand($items)];
    }
}
