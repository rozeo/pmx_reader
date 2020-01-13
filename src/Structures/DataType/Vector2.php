<?php

namespace App\Structures\DataType;

use JsonSerializable;

class Vector2 implements JsonSerializable
{

    private float $x;
    private float $y;

    public function __construct(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getX() { return $this->x; }
    public function getY() { return $this->y; }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
        ];
    }
}
