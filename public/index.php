<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application;
use Database\Seed;

$app = new Application();
$app->run();
$seed = new Seed();
$seed->run();