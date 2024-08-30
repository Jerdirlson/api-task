<?php

use DI\Container;
use Slim\Factory\AppFactory;
use app\Repositories\CharacterRepository;
use app\Repositories\CharacterRepositoryInterface;
use app\Repositories\EquipmentRepository;
use app\Repositories\EquipmentRepositoryInterface;
use app\Repositories\FactionRepository;
use app\Repositories\FactionRepositoryInterface;
use app\Core\Router;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

// Crear el contenedor de DI
$container = new Container();

$container->set(CharacterRepositoryInterface::class, function () {
    return new CharacterRepository();
});

$container->set(EquipmentRepositoryInterface::class, function () {
    return new EquipmentRepository();
});

$container->set(FactionRepositoryInterface::class, function () {
    return new FactionRepository();
});

// Cargar variables de entorno desde el archivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

AppFactory::setContainer($container);
$app = AppFactory::create();

Router::init($app);


$app->run();
