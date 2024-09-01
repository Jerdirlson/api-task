<?php

namespace app\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use DI\Container;
use app\Core\Router;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Firebase\JWT\JWT;

class RouterMiddlewareTest extends TestCase
{
    public function testAccessWithoutAuthentication()
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        Router::init($app);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/user');
        $response = $app->handle($request);

        $this->assertEquals(401, $response->getStatusCode());
    }
}
