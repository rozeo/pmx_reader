<?php

namespace App\Structures\DataType;

use JsonSerializable;

class Vector4 implements JsonSerializable
{

    private float $x;
    private float $y;
    private float $z;
    private float $a;

    public function __construct(float $x, float $y, float $z, float $a)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->a = $a;
    }

    public function getX() { return $this->x; }
    public function getY() { return $this->y; }
    public function getZ() { return $this->z; }
    public function getA() { return $this->a; }

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
            'a' => $this->a,
        ];
    }
}
