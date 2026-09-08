<?php

declare(strict_types=1);

use App\Application;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as CapsuleManager;

require dirname(__DIR__) . '/vendor/autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$builder = new ContainerBuilder();

$builder->useAutowiring(true);

$builder->addDefinitions(
    dirname(__DIR__) . '/config/container.php'
);

$container = $builder->build();

$container->get(CapsuleManager::class);

$application = $container->get(Application::class);
$application->run();
