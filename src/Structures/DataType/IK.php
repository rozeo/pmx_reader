<?php

namespace App\Structures\DataType;

use JsonSerializable;

class IK implements JsonSerializable
{
    private int $targetIndex;
    private int $loopCount;
    private float $limitRadian;
    private int $linkCount;
    private array/*<IKLink>*/ $links;

    public function __construct(
        int $targetIndex,
        int $loopCount,
        float $limitRadian,
        int $linkCount,
        array $links
    )
    {
        $this->targetIndex = $targetIndex;
        $this->loopCount = $loopCount;
        $this->limitRadian = $limitRadian;
        $this->linkCount = $linkCount;
        $this->links = $links;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'target' => $this->targetIndex,
            'loop' => $this->loopCount,
            'limit_rad' => $this->limitRadian,
            'links' => $this->links,
        ];
    }
}
