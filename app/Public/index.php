<?php

use app\Core\Router;
use app\Model\Interfaces\CharacterRepositoryInterface;
use app\Model\Interfaces\EquipmentRepositoryInterface;
use app\Model\Interfaces\FactionRepositoryInterface;
use app\Model\Repositories\CharacterRepository;
use app\Model\Repositories\EquipmentRepository;
use app\Model\Repositories\FactionRepository;
use DI\Container;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Predis\Client;
use app\Core\Redis;

require_once __DIR__ . '/../vendor/autoload.php';

// Crear el contenedor de DI
$container = new Container();

$container->set(CharacterRepositoryInterface::class, function ($c) {
    return new CharacterRepository($c->get(Client::class));
});

$container->set(EquipmentRepositoryInterface::class, function ($c) {
    return new EquipmentRepository($c->get(Client::class));
});

$container->set(FactionRepositoryInterface::class, function ($c) {
    return new FactionRepository($c->get(Client::class));
});

$container->set(Client::class, function () {
    $redis = new Redis();
    return $redis->initConnection();
});

// Cargar variables de entorno desde el archivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

AppFactory::setContainer($container);
$app = AppFactory::create();

Router::init($app);

$app->run();
