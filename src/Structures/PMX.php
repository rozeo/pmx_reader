<?php

namespace App\Structures;

use App\BinaryReader;

use App\Structures\DataType\IndexWeightPair;
use App\Structures\DataType\Vector2;
use App\Structures\DataType\Vector3;
use App\Structures\DataType\Vector4;
use App\Structures\DataType\Vertex;
use App\Structures\DataType\Material;
use App\Structures\DataType\Bone;
use App\Structures\DataType\IK;
use App\Structures\DataType\IKLink;

use JsonSerializable;

class PMX implements JsonSerializable
{
    const GLOBALS_TEXT_FORMAT = 0;
    const GLOBALS_ADDITION_VEC4S = 1;
    const GLOBALS_VERTEX_INDEX_TYPE = 2;
    const GLOBALS_TEXTURE_INDEX_TYPE = 3;
    const GLOBALS_MATERIAL_INDEX_TYPE = 4;
    const GLOBALS_BONE_INDEX_TYPE = 5;
    const GLOBALS_MORPH_INDEX_TYPE = 6;
    const GLOBALS_RIGIDBODY_INDEX_TYPE = 7;

    private $reader;

    private $version;

    private $globalsCount;
    private $globals;

    private $nameJapanese;
    private $nameTraditional;

    private $commentJapanese;
    private $commentTraditional;

    private $vertices;

    private $surfaces;

    private $textures;

    private $materials;

    private $bones;

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
            'surfaces' => $this->surfaces,
            'textures' => $this->textures,
            'materials' => $this->materials,
            'bones' => $this->bones, 
        ];
    }

    public function load() 
    {
        $this->checkFileSignature();
    
        $this->loadVersion();

        $this->loadGlobals();

        $this->loadNames();

        $this->loadVertices();

        $this->loadSurfaces();

        $this->loadTextures();

        $this->loadMaterials();

        $this->loadBones();
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
    }

    private function loadValueIndex(int $globalsIndex)
    {
        switch($this->globals[$globalsIndex])
        {
            case 1:
                if ($globalsIndex === PMX::GLOBALS_VERTEX_INDEX_TYPE) {
                    return $this->reader->readUInt8();
                } else {
                    return $this->reader->readInt8();
                }

            case 2:
                if ($globalsIndex === PMX::GLOBALS_VERTEX_INDEX_TYPE) {
                    return $this->reader->readUInt16();
                } else {
                    return $this->reader->readInt16();
                }

            case 4:
                return $this->reader->readInt32();
        }
    }

    private function loadVertices()
    {
        $vertexCount = $this->reader->readInt32();
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
            for ($j = 0; $j < $this->globals[PMX::GLOBALS_ADDITION_VEC4S]; $j++) {
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
                        new IndexWeightPair($this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE), 1.0), 
                    ];
                    break;

                // BDEF2
                case 1:
                    $i1 = $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE);
                    $i2 = $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE);
                    $w1 = $this->reader->readFloat32();

                    $deform = [
                        new IndexWeightPair($i1, $w1),
                        new IndexWeightPair($i2, 1.0 - $w1),  
                    ];
                    break;
                
                // BDEF4, QDEF
                case 2:
                case 4: 
                    $i1 = $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE);
                    $i2 = $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE);
                    $i3 = $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE);
                    $i4 = $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE);

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

                // SDEF
                case 3:
                    $i1 = $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE);
                    $i2 = $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE);
                    $w1 = $this->reader->readFloat32();
                    $w2 = 1.0 - $w1;
                     
                    $c = new Vector3(
                        $this->reader->readFloat32(),
                        $this->reader->readFloat32(),
                        $this->reader->readFloat32()
                    );

                    $r0 = new Vector3(
                        $this->reader->readFloat32(),
                        $this->reader->readFloat32(),
                        $this->reader->readFloat32()
                    );
                    
                    $r1 = new Vector3(
                        $this->reader->readFloat32(),
                        $this->reader->readFloat32(), 
                        $this->reader->readFloat32()
                    );

                    $deform = [
                        new IndexWeightPair($i1, $w1),
                        new IndexWeightPair($i2, $w2),
                        $c, $r0, $r1,
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

        echo 'Vertices count: ' . count($this->vertices) . "\n";
    }

    private function loadSurfaces()
    {
        $surfacesCount = $this->reader->readInt32();

        echo 'Surfaces count, ' . $surfacesCount . "\n";
        $this->surfaces = [];

        $surfacesCount /= 3;
        for ($i = 0; $i < $surfacesCount; $i++) {
            $this->surfaces[] = [
                $this->loadValueIndex(PMX::GLOBALS_VERTEX_INDEX_TYPE),
                $this->loadValueIndex(PMX::GLOBALS_VERTEX_INDEX_TYPE),
                $this->loadValueIndex(PMX::GLOBALS_VERTEX_INDEX_TYPE)
            ];
        }
    }

    private function loadTextures()
    {
        $texturesCount = $this->reader->readInt32();
        $this->textures = [];

        for ($i = 0; $i < $texturesCount; $i++) {
            $this->textures[] = $this->loadTextData();
        }

        print_r($this->textures);
    }

    private function loadMaterials()
    {
        $materialsCount = $this->reader->readInt32();
        $this->materials = [];
        
        for ($i = 0; $i < $materialsCount; $i++) {
            $this->materials[] = new Material(
                $this->loadTextData(),
                $nameTraditional = $this->loadTextData(),
                new Vector4(
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32()
                ),
                new Vector3(
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32()
                ),
                $this->reader->readFloat32(),
                new Vector3(
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32()
                ),
                $this->reader->readInt8(),
                new Vector4(
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32()
                ),
                $this->reader->readFloat32(),
                $this->loadValueIndex(PMX::GLOBALS_TEXTURE_INDEX_TYPE),
                $this->loadValueIndex(PMX::GLOBALS_TEXTURE_INDEX_TYPE),
                $this->reader->readUInt8(),
                ($toon = $this->reader->readInt8()),
                ($toon === 0)? 
                    $this->loadValueIndex(PMX::GLOBALS_TEXTURE_INDEX_TYPE):
                    $this->reader->readUInt8(),
                $this->loadTextData(),
                $this->reader->readInt32()
            );
        }

        print_r($this->materials);
    }

    public function loadBones()
    {
        $bonesCount = $this->reader->readInt32();
        $this->bones = [];

        echo 'Bones count: ' . $bonesCount . "\n";

        $handle = $this;

        for ($i = 0; $i < $bonesCount; $i++) {
            $this->bones = new Bone(
                $this->loadTextData(),
                $this->loadTextData(),
                new Vector3(
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32()
                ),
                $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE),
                $this->reader->readInt32(),
                ($flags = $this->reader->readUInt16()),
                ($flags & 0b1) ? $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE):
                    new Vector3(
                        $this->reader->readFloat32(),
                        $this->reader->readFloat32(),
                        $this->reader->readFloat32()
                    ),
                new IndexWeightPair($this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE), 
                    $this->reader->readFloat32()
                ),
                new Vector3(
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32()
                ),
                new Vector3(
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32()
                ),

                new Vector3(
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32(),
                    $this->reader->readFloat32()
                ),
                $this->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE),
                (function () use ($handle) {
                    return new IK(
                        $handle->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE),
                        $handle->reader->readInt32(),
                        $handle->reader->readFloat32(),
                        ($linksCount = $handle->reader->readInt32()),
                        (function () use ($handle, $linksCount) {
                            $links = [];
                            echo "linksCount" . $linksCount . "\n";
                            for ($j = 0; $j < $linksCount; $j++) {
                                $links[] = new IKLink(
                                    $handle->loadValueIndex(PMX::GLOBALS_BONE_INDEX_TYPE),
                                    $handle->reader->readUInt8(),
                                    new Vector3(
                                        $handle->reader->readFloat32(),
                                        $handle->reader->readFloat32(),
                                        $handle->reader->readFloat32()
                                    ),
                                    new Vector3(
                                        $handle->reader->readFloat32(),
                                        $handle->reader->readFloat32(),
                                        $handle->reader->readFloat32()
                                    ),
                                );
                            }

                            return $links;
                        })()
                    );
                })()
            );

            print_r($this->bones[$i]);
        }

        print_r($this->bones);
    }
}
