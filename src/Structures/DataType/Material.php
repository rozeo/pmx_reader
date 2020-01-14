<?php

namespace App\Structures\DataType;

use JsonSerializable;

class Material
{

    private string $nameJapanese;
    private string $nameTraditional;

    private Vector4 $diffuseColour;
    private Vector3 $specular;
    private float $specularStlength;

    private Vector3 $ambientColour;
    private int $drawFlags;

    private Vector4 $edgeColour;
    private float $edgeScale;

    private int $textureIndex;
    private int $environmentIndex;

    private int $environmentBlendMode;
    private int $toonReference;
    private int $toonValue;
    
    private string $metaData;

    private int $surfaceCount;
    
    public function __construct(
        string $nameJapanese,
        string $nameTraditional,
        Vector4 $diffuseColour,
        Vector3 $specular,
        float $specularStlength,
        Vector3 $ambientColour,
        int $drawFlags,
        Vector4 $edgeColour,
        float $edgeScale,
        int $textureIndex,
        int $environmentIndex,
        int $environmentBlendMode,
        int $toonReference,
        int $toonValue,
        string $metaData,
        $surfaceCount
    )
    {
        $this->nameJapanese = $nameJapanese;
        $this->nameTraditional = $nameTraditional;

        $this->diffuseColour = $diffuseColour;
        $this->specular = $specular;
        $this->specularStlength = $specularStlength;

        $this->ambientColour = $ambientColour;
        $this->drawFlags = $drawFlags;

        $this->edgeColour = $edgeColour;
        $this->edgeScale = $edgeScale;

        $this->textureIndex = $textureIndex;
        $this->environmentIndex = $environmentIndex;

        $this->environmentBlendMode = $environmentBlendMode;
        $this->toonReference = $toonReference;
        $this->toonValue = $toonValue;
    
        $this->metaData = $metaData;

        $this->surfaceCount = $surfaceCount;
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
            'diffuse' => $this->diffuseColour,
            'specular' => $this->specular,
            'specular_stlength' => $this->specularStlength,
            'ambient' => $this->ambientColour,
            'flags' => $this->drawFlags,
            'edge' => $this->edgeColour,
            'edge_scale' => $this->edgeScale,
            'texture' => $this->textureIndex,
            'environment' => $this->environmentIndex,
            'blend_mode' => $this->environmentBlendMode,
            'toon' => $this->toonReference,
            'toon_value' => $this->toonValue,
            'meta' => $this->metaData,
            'surface_count' => $this->surfaceCount,
        ];
    }
}
