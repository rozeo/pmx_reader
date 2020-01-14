<?php

namespace App\Structures\DataType;

use JsonSerializable;

class IKLink implements JsonSerializable
{
    private int $index;
    private int $hasLimit;
    private Vector3 $limitAngleMin;
    private Vector3 $limitAngleMax;

    public function __construct(
        int $index,
        int $hasLimit,
        Vector3 $limitAngleMin,
        Vector3 $limitAngleMax
    ) {
        $this->index = $index;
        $this->hasLimit = $hasLimit;
        $this->limitAngleMin = $limitAngleMin;
        $this->limitAngleMax = $limitAngleMax;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'index' => $this->index,
            'has_limit' => $this->hasLimit,
            'limit_angle' => [
                'min' => $this->limitAngleMin,
                'max' => $this->limitAngleMax,
            ],
        ];
    }
}
