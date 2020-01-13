<?php

namespace App\Structures;

use App\BinaryReader;

use App\Structures\DataType\IndexWeightPair;
use App\Structures\DataType\Vector2;
use App\Structures\DataType\Vector3;
use App\Structures\DataType\Vector4;
use App\Structures\DataType\Vertex;

use JsonSerializable;

class PMX implements JsonSerializable
{
    private $reader;

    private $version;

    private $globalsCount;
    private $globals;

    private $nameJapanese;
    private $nameTraditional;

    private $commentJapanese;
    private $commentTraditional;

    private $vertices;

    public function __construct(&$fp)
    {
        $this->reader = new BinaryReader($fp, BinaryReader::ENDIAN_LITTLE);
        $this->load();
    }

    public function jsonSerialize()
    {
        return $this->getData();
    }

    public function getData()
    {
        return [
            'version' => sprintf('%.1f', $this->version),
            'globals' => $this->globals,
            'name_jp' => $this->nameJapanese,
            'name_traditional' => $this->nameTraditional,
            'comment_jp' => $this->commentJapanese,
            'comment_traditional' => $this->commentTraditional,
            'vertices' => $this->vertices,
        ];
    }

    public function load() 
    {
        $this->checkFileSignature();
    
        $this->loadVersion();

        $this->loadGlobals();

        $this->loadNames();

        $this->loadVertices();
    }

    private function loadTextData()
    {
        $length = $this->reader->readUInt32();
        if ($this->globals[0] === 0) {
            return $this->reader->readStringAsUtf16LE($length);
        } elseif ($this->globals[0] === 1) {
            return $this->reader->readStringAsUtf8($length);
        }
    }

    private function checkFileSignature()
    {
        if ($this->reader->readStringAsAscii(4) !== 'PMX ') {
            throw new InvalidDataException('Unmatched file format.');
        }
    }

    private function loadVersion()
    {
        $this->version = $this->reader->readSingle();
    }

    private function loadGlobals()
    {
        $this->globals = array_pad([], 8, 0);

        $this->globalsCount = $this->reader->readUInt8();

        for ($i = 0; $i < $this->globalsCount; $i++) {
            $this->globals[$i] = $this->reader->readUInt8();
        }
    }

    private function loadNames()
    {
        $this->nameJapanese = $this->loadTextData();
        $this->nameTraditional = $this->loadTextData();

        $this->commentJapanese = $this->loadTextData();
        $this->commentTraditional = $this->loadTextData();

        print_r([$this->nameJapanese, $this->commentTraditional]);
    }

    private function loadBoneIndex()
    {
        switch($this->globals[5])
        {
            case 1:
                return $this->reader->readUInt8();

            case 2:
                return $this->reader->readInt16();

            case 4:
                return $this->reader->readInt32();
        }
    }

    private function loadVertices()
    {
        $vertexCount = $this->reader->readUInt32();
        $this->vertices = [];

        for ($i = 0; $i < $vertexCount; $i++) {
            $px = $this->reader->readFloat32();
            $py = $this->reader->readFloat32();
            $pz = $this->reader->readFloat32();

            $nx = $this->reader->readFloat32();
            $ny = $this->reader->readFloat32();
            $nz = $this->reader->readFloat32();

            $ux = $this->reader->readFloat32();
            $uy = $this->reader->readFloat32();

            $additionVec4s = [];
            for ($j = 0; $j < $this->globals[1]; $j++) {
                $ax = $this->reader->readFloat32();
                $ay = $this->reader->readFloat32();
                $az = $this->reader->readFloat32();
                $aa = $this->reader->readFloat32();

                $additionVec4s[] = new Vector4($ax, $ay, $az, $aa);
            }

            $deformType = $this->reader->readUInt8();
            $deform = [];

            switch($deformType)
            {
                // BDEF1
                case 0:
                    $deform = [
                        new IndexWeightPair($this->loadBoneIndex(), $this->reader->readFloat32()), 
                    ];
                    break;

                // BDEF2
                case 1:
                    $i1 = $this->loadBoneIndex();
                    $i2 = $this->loadBoneIndex();
                    $w1 = $this->reader->readFloat32();

                    $deform = [
                        new IndexWeightPair($i1, $w1),
                        new IndexWeightPair($i2, 1.0 - $w1),  
                    ];
                    break;
                
                // BDEF4, QDEF
                case 2:
                case 4: 
                    $i1 = $this->loadBoneIndex();
                    $i2 = $this->loadBoneIndex();
                    $i3 = $this->loadBoneIndex();
                    $i4 = $this->loadBoneIndex();

                    $w1 = $this->reader->readFloat32();
                    $w2 = $this->reader->readFloat32();
                    $w3 = $this->reader->readFloat32();
                    $w4 = $this->reader->readFloat32();

                    $deform = [
                        new IndexWeightPair($i1, $w1),
                        new IndexWeightPair($i2, $w2),
                        new IndexWeightPair($i3, $w3),
                        new IndexWeightPair($i4, $w4),
                    ];
                    break;
            }

            $edgeScale = $this->reader->readFloat32();

            $this->vertices[] = new Vertex(
                new Vector3($px, $py, $pz),
                new Vector3($nx, $ny, $nz),
                new Vector2($ux, $uy),
                $additionVec4s,
                $deformType,
                $deform,
                $edgeScale
            );
        }
    }
}
