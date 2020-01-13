<?php

namespace App;

use IntlChar;
use App\Utf8ByteReader;
    
class BinaryReader extends \PhpBinaryReader\BinaryReader
{

    const ENDIAN_BIG = \PhpBinaryReader\Endian::ENDIAN_BIG;
    const ENDIAN_LITTLE = \PhpBinaryReader\Endian::ENDIAN_LITTLE;

    public function __construct($resource, $endian)
    {
        parent::__construct($resource, $endian);
    }

    public function readStringAsAscii(int $byteLength)
    {
        return $this->readString($byteLength);
    }

    public function readStringAsUtf16LE(int $byteLength)
    {
        return $this->readStringAsUtf16($byteLength, true);
    }

    public function readStringAsUtf16BE(int $byteLength)
    {
        return $this->readStringAsUtf16($byteLength, false);
    }

    public function readStringAsUtf16(int $byteLength, bool $littleEndian = false)
    {   
        
        $readBytes = $this->readFromHandle($byteLength);
        $str = '';

        for ($i = 0; $i < $byteLength; $i += 2)
        {
            $b1 = ord($readBytes[$i]);
            $b2 = ord($readBytes[$i + 1]);
            
            $codePoint = $littleEndian ? $b2 * 256 + $b1: $b1 * 256 + $b2;

            $str .= IntlChar::chr($codePoint);
        }

        return $str;
    }

    public function readStringAsUtf8BE(int $byteLength)
    {
        return $this->readStringAsUtf8($byteLength, false);
    }

    public function readStringAsUtf8LE(int $byteLength)
    {
        return $this->readStringAsUtf8($byteLength, true);
    }

    public function readStringAsUtf8(int $byteLength, $littleEndian = false)
    {
        return Utf8ByteReader::load($this->readFromHandle($byteLength), $littleEndian);
    }

    public function readFloat32()
    {
        return $this->readSingle();
    }
}
