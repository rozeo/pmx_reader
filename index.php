<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\PMXReader;

ini_set('memory_limit', '8G');

$structure = PMXReader::Load(__DIR__ . '/Hatsune miku Magical mirai 2019.pmx');

file_put_contents('pmx.json', json_encode($structure, JSON_PRETTY_PRINT));
