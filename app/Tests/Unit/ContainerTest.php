<?php

namespace app\Tests\Unit;
use app\Model\Interfaces\CharacterRepositoryInterface;
use app\Model\Interfaces\EquipmentRepositoryInterface;
use app\Model\Interfaces\FactionRepositoryInterface;
use app\Model\Repositories\CharacterRepository;
use app\Model\Repositories\EquipmentRepository;
use app\Model\Repositories\FactionRepository;
use PHPUnit\Framework\TestCase;
use DI\Container;
use Predis\Client;
use Slim\Factory\AppFactory;
use app\Core\Router;

class ContainerTest extends TestCase
{
    public function testAppInitialization()
    {
        $redisMock = $this->createMock(Client::class);

        $container = new Container();

        $container->set(CharacterRepositoryInterface::class, function ($c) use ($redisMock) {
            return new CharacterRepository($redisMock);
        });

        $container->set(EquipmentRepositoryInterface::class, function ($c) use ($redisMock) {
            return new EquipmentRepository($redisMock);
        });

        $container->set(FactionRepositoryInterface::class, function ($c) use ($redisMock) {
            return new FactionRepository($redisMock);
        });

        $container->set(Client::class, function () use ($redisMock) {
            return $redisMock;
        });

        // Simular la carga de las variables de entorno (sin usar Dotenv)
        putenv('DB_HOST=api-task-db-1');
        putenv('DB_NAME=lotr');
        putenv('DB_USER=root');
        putenv('DB_PASS=root');

        AppFactory::setContainer($container);
        $app = AppFactory::create();

        Router::init($app);

        $this->assertNotNull($app);

    }
}
