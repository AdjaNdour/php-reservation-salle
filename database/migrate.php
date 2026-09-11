<?php

// declare(strict_types=1);

// use DI\ContainerBuilder;
// use Dotenv\Dotenv;
// use Illuminate\Database\Capsule\Manager as CapsuleManager;

// require_once dirname(__DIR__) . '/vendor/autoload.php';

// $dotenv = Dotenv::createImmutable(dirname(__DIR__));
// $dotenv->load();

// $builder = new ContainerBuilder();
// $builder->useAutowiring(true);
// $builder->addDefinitions(dirname(__DIR__) . '/config/container.php');
// $container = $builder->build();
// $container->get(CapsuleManager::class);

// $migrations = glob(dirname(__DIR__) . '/database/migrations/*.php');

// if ($migrations === false) {
//     echo "Aucune migration trouvée.\n";
//     exit(1);
// }

// sort($migrations);

// foreach ($migrations as $migration) {
//     echo "Migration : " . basename($migration) . "\n";

//     require $migration;

//     echo "Migration exécutée avec succès.\n";
// }

// echo "Toutes les migrations ont été exécutées.\n";




declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as CapsuleManager;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->addDefinitions(dirname(__DIR__) . '/config/container.php');

$container = $builder->build();

/** @var CapsuleManager $capsule */
$capsule = $container->get(CapsuleManager::class);

// Récupération des fichiers de migration
$migrations = glob(dirname(__DIR__) . '/database/migrations/*.php');

if ($migrations === false || count($migrations) === 0) {
    echo "Aucune migration trouvée." . PHP_EOL;
    exit(1);
}

// Exécution dans l'ordre des fichiers
sort($migrations);

foreach ($migrations as $migration) {
    echo "Migration : " . basename($migration) . PHP_EOL;

    // Le fichier retourne une instance de la migration
    $migrationInstance = require $migration;

    // Vérification de l'interface
    if (!$migrationInstance instanceof \Database\MigrationInterface) {
        echo "Erreur : " . basename($migration)
            . " n'implémente pas MigrationInterface." . PHP_EOL;

        exit(1);
    }

    // Exécution de la migration
    $migrationInstance->up($capsule->schema());

    echo "Migration exécutée avec succès." . PHP_EOL;
}

echo "Toutes les migrations ont été exécutées." . PHP_EOL;

