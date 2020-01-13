<?php

namespace App\Structures\DataType;

use JsonSerializable;

class Vertex implements JsonSerializable
{
    private $position;
    private $normal;
    private $uv;

    private $additionVec4s;
    
    private $deformType;
    private $deform;
    private $edgeScale;

    public function __construct(
        Vector3 $position, Vector3 $normal, Vector2 $uv,
        $additionVec4s, $deformType, $deform, float $edgeScale
    ) {
        $this->position = $position;
        $this->normal = $normal;
        $this->uv = $uv;

        $this->additionVec4s = $additionVec4s;
        $this->deformType = $deformType;
        $this->deform = $deform;

        $this->edgeScale = $edgeScale;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'position' => $this->position->toArray(),
            'normal' => $this->normal->toArray(),
            'uv' => $this->uv->toArray(),
            
            'addition_vec4' => array_map(function($v) { return $v->toArray(); }, $this->additionVec4s),

            'deformType' => $this->deformType,
            'deform' => array_map(function($v) {return $v->toArray(); }, $this->deform),
            'edgeScale' => $this->edgeScale,
        ];
    }
}
