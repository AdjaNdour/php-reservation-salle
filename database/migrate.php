<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

require_once dirname(__DIR__) . '/config/database.php';

$migrations = glob(dirname(__DIR__) . '/database/migrations/*.php');

if ($migrations === false) {
    echo "Aucune migration trouvée.\n";
    exit(1);
}

sort($migrations);

foreach ($migrations as $migration) {
    echo "Migration : " . basename($migration) . "\n";

    require $migration;

    echo "Migration exécutée avec succès.\n";
}

echo "Toutes les migrations ont été exécutées.\n";