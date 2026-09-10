<?php

declare(strict_types=1);

if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

require dirname(__DIR__) . '/vendor/autoload.php';

// Si le fichier .env existe, charger les variables d'environnement
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

// Si DB_HOST pointe sur 'mysql' (nom de service Docker) mais que l'hôte n'est pas résoluble
// (exécution des tests sur la machine hôte), basculer sur 127.0.0.1.
if (empty($_ENV['DB_HOST']) || ($_ENV['DB_HOST'] === 'mysql' && gethostbyname('mysql') === 'mysql')) {
    $_ENV['DB_HOST'] = '127.0.0.1';
    putenv('DB_HOST=127.0.0.1');
}

use Illuminate\Database\Capsule\Manager as Capsule;

// Initialisation du résolveur Eloquent en mémoire pour permettre le formatage
// et le casting des dates sans dépendance à un serveur MySQL actif.
$capsule = new Capsule();
$capsule->addConnection([
    'driver'   => 'sqlite',
    'database' => ':memory:',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
