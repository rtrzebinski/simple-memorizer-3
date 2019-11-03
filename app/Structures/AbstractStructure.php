<?php

namespace App\Structures;

/**
 * Structures are denormalized results of db queries.
 * Class AbstractStructure
 * @package App\Structures
 */
abstract class AbstractStructure
{
    /**
     * Use to initialize fields from DB query like:
     * $query->mapInto(ConcreteStructure::class)
     *
     * AbstractStructure constructor.
     * @param \stdClass $data
     */
    public function __construct(\stdClass $data = null)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
