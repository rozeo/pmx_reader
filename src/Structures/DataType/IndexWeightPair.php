<?php

namespace App\Structures\DataType;

class IndexWeightPair
{
    private $index;
    private $weight;

    public function __construct(int $index, float $weight)
    {
        $this->index = $index;
        $this->weight = $weight;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'index' => $this->index,
            'weight' => $this->weight,
        ];
    }
}
