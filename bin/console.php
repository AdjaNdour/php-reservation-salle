<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$capsule = require_once dirname(__DIR__) . '/config/database.php';

$command = $argv[1] ?? null;

switch ($command) {
    case 'migrate':
        require_once dirname(__DIR__) . '/database/migrate.php';
        break;
    case 'seed':
        require_once dirname(__DIR__) . '/database/Seed.php';
        break;
    default:
        echo "Commandes disponibles: migrate, seed \n";
        exit(1);
}