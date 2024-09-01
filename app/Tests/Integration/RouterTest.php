<?php

namespace app\Tests\Integration;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use DI\Container;
use app\Core\Router;

class RouterTest extends TestCase
{
    public function testRoutesInitialization()
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $initializedApp = Router::init($app);

        $this->assertInstanceOf(App::class, $initializedApp);
    }
}
