<?php

namespace App\Structures;

use App\BinaryReader;

class PMX
{
    private $reader;

    private $version;

    private $globalsCount;
    private $globals;

    private $nameJapanese;
    private $nameTraditional;

    private $commentJapanese;
    private $commentTraditional;

    public function __construct(&$fp)
    {
        $this->reader = new BinaryReader($fp, BinaryReader::ENDIAN_LITTLE);
        $this->load();
    }

    public function getData()
    {
    
    }

    public function load() 
    {
        $this->checkFileSignature();
    
        $this->loadVersion();

        $this->loadGlobals();

        $this->loadNames();
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
}
