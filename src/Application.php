<?php

namespace App;

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

class Application
{
    private Capsule $capsule;

    public function __construct()
    {
        // 1. Charger .env
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // 2. Créer Capsule
        $this->capsule = new Capsule();

        // 3. Configurer la base de données
        $this->capsule->addConnection([
            'driver'   => $_ENV['DB_DRIVER'],
            'host'     => $_ENV['DB_HOST'],
            'port'     => $_ENV['DB_PORT'],
            'database' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
        ]);
        // 4. Initialiser Eloquent
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    function run()
    {
        // 5. Démarrer l'application
        try {
            $this->capsule->connection()->getPdo();
            echo "Connexion réussie !";
            echo "Application is running!";
        } catch (\Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function runMigrations()
    {

        require_once dirname(__DIR__) . '/database/migrations/001_create_salles_table.php';
        require_once dirname(__DIR__) . '/database/migrations/002_create_reservations_table.php';
        require_once dirname(__DIR__) . '/database/migrations/003_create_utilisateurs_table.php';

        echo "Migration réussie !";
    }
}
