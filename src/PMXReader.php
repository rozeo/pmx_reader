<?php

namespace App;

use file_exists;
use InvalidArgumentException;

class PMXReader
{
    public static function Load(string $fileName)
    {
        if (!file_exists($fileName)) {
            throw InvalidArgumentException("File not found, $fileName");
        }
        
        $fp = fopen($fileName, 'rb');
        return new Structures\PMX($fp);
    }
}
