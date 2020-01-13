<?php

namespace App\Structures\DataType;

use JsonSerializable;

class Vector3 implements JsonSerializable
{

    private float $x;
    private float $y;
    private float $z;

    public function __construct(float $x, float $y, float $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    public function getX() { return $this->x; }
    public function getY() { return $this->y; }
    public function getZ() { return $this->z; }


    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
        ];
    }
}
