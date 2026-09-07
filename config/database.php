<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

// Charger les variables d'environnement si non encore chargées
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile) && !isset($_ENV['DB_DRIVER'])) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

return (static function (): Capsule {
    
    static $capsule = null;

    if ($capsule !== null) {
        return $capsule;
    }

    $driver = $_ENV['DB_DRIVER'] ?? 'mysql';

    $config = match ($driver) {
        'pgsql' => [
            'driver'    => 'pgsql',
            'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port'      => $_ENV['DB_PORT'] ?? '5432',
            'database'  => $_ENV['DB_DATABASE'] ?? 'reservation_salles',
            'username'  => $_ENV['DB_USERNAME'] ?? 'postgres',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'charset'   => 'utf8',
            'prefix'    => '',
            'schema'    => 'public',
            'sslmode'   => 'prefer',
        ],
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => $_ENV['DB_DATABASE'] ?? dirname(__DIR__) . '/database/database.sqlite',
            'prefix'   => '',
        ],
        default => [
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port'      => $_ENV['DB_PORT'] ?? '3306',
            'database'  => $_ENV['DB_DATABASE'] ?? 'reservation_salles',
            'username'  => $_ENV['DB_USERNAME'] ?? 'root',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ],
    };

    $capsule = new Capsule();
    $capsule->addConnection($config);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    try {
        $capsule->getConnection()->getPdo();
    } catch (\Throwable $e) {
        throw new \RuntimeException(
            sprintf("Erreur de connexion à la base de données (%s) : %s", $driver, $e->getMessage()),
            (int) $e->getCode(),
            $e
        );
    }

    return $capsule;
})();
