<?php

namespace Permafrost\RayScan\Support;

class ProgressData
{
    /** @var int */
    public $total = 0;

    /** @var int */
    public $current = 0;

    /** @var int */
    public $position = 0;

    /** @var int */
    public $scale = 0;

    public static function create(array $data): self
    {
        $result = new static();

        foreach($data as $prop => $value) {
            if (property_exists($result, $prop)) {
                $result->$prop = $value;
            }
        }

        $result->position = 1;

        return $result;
    }
}
