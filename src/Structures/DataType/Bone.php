<?php

namespace App\Structures\DataType;

use JsonSerializable;

class Bone implements JsonSerializable
{
    private string $nameJapanese;
    private string $nameTraditional;

    private Vector3 $position;
    private int $parentBoneIndex;
    private int $layer;
    private int $flags;

    private $tailPosition;

    private IndexWeightPair $inheritBone;

    private Vector3 $fixedAxis;

    private Vector3 $localCoOrdinateX;
    private Vector3 $localCoOrdinateY;

    private $externalParent;

    private IK $ik;


    public function __construct(
        string $nameJapanese,
        string $nameTraditional,
        Vector3 $position,
        int $parentBoneIndex,
        int $layer,
        int $flags,
        $tailPosition,
        IndexWeightPair $inheritBone,
        Vector3 $fixedAxis,
        Vector3 $localCoOrdinateX,
        Vector3 $localCoOrdinateY,
        $externalParent,
        IK $ik
    ) {
        $this->nameJapanese = $nameJapanese;
        $this->nameTraditional = $nameTraditional;
        $this->position = $position;
        $this->parentBoneIndex = $parentBoneIndex;
        $this->layer = $layer;
        $this->flags = $flags;
        $this->tailPosition = $tailPosition;
        $this->inheritBone = $inheritBone;
        $this->fixedAxis = $fixedAxis;
        $this->localCoOrdinateX = $localCoOrdinateX;
        $this->localCoOrdinateY = $localCoOrdinateY;
        $this->externalParent = $externalParent;
        $this->ik = $ik;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'name_jp' => $this->nameJapanese,
            'name_traditional' => $this->nameTraditional,
            'position' => $this->position,
            'parent' => $this->parentBoneIndex,
            'layer' => $this->layer,
            'flags' => $this->flags,
            'tail' => $this->tailPosition,
            'inherit' => $this->inheritBone,
            'fixed_axis' => $this->fixedAxis,
            'local_co_ordinate' => [
                'x' => $this->localCoOrdinateX,
                'y' => $this->localCoOrdinateY,
            ],
            'external' => $this->externalParent,
            'IK' => $this->ik,
        ];
    }
        
}
