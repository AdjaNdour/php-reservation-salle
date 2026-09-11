<?php

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

$capsule = $container->get(CapsuleManager::class);

$command = $argv[1] ?? null;

switch ($command) {
    case 'migrate':
        require_once dirname(__DIR__) . '/database/migrate.php';
        break;

    case 'seed':
        require_once dirname(__DIR__) . '/database/Seed.php';
        (new \Database\Seed())->run();
        break;

    default:
        echo "Commandes disponibles : migrate, seed" . PHP_EOL;
        exit(1);
}