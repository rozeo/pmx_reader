<?php

namespace App;
use IntlChar;

class Utf8ByteReader
{

    public static function load(array $arr, $littleEndian = false)
    {
        $count = count($arr);
        $str = '';
        $width = 0;
        $_str = [];

        for($i = 0; $i < $count; $i++) {
            $_str[] = $cp = $arr[$i];
            
            if (!$littleEndian) {
                $cp = $arr[$i - $width];
            }

            print_r($_str);
            $match = false;

            switch($width)
            {
                case 0:
                    if ($cp > 0x7F) {
                        $width++;
                        break;
                    }
                    $match = true;
                    break;

                case 1:
                    if ($cp < (0xC2) || $cp > (0xDF)) {
                        $width++;
                        break;
                    }
                    $match = true;
                    break;

                case 2:
                    if ($cp < 0xE0 || $cp > 0xEF) {
                        $width++;
                        break;
                    }
                    $match = true;
                    break;

                case 3:
                    $match = true;
                    break;
            }

            if ($match) {
                if ($littleEndian) $_str = \array_reverse($_str);

                $str .= self::convDec2Str($_str);
                $width = 0;
                $_str = [];
            }
        }
        return $str;
    }

    private static function convDec2Str(array $arr)
    {
        $bitMask = [
            0b01111111,
            0b00011111,
            0b00001111,
            0b00000111,
        ];

        $width = count($arr);
        $t = decbin(($arr[0] & $bitMask[$width - 1]));
        for ($i = 1; $i < $width; $i++) {
            $t .= str_pad(decbin($arr[$i] & 0x3F), 6, '0', STR_PAD_LEFT); 
        }
        return IntlChar::chr(bindec($t));
    }
}

// echo Utf8ByteReader::load([0x88, 0x82, 0xE3], true);
