<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;

final class Application
{
    private Capsule $capsule;

    public function __construct()
    {
        $this->capsule = require dirname(__DIR__) . '/config/database.php';
    }

    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }
}