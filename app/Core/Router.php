<?php

namespace app\Core;

use Predis\Client;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use app\Core\RoleMiddleware;
use app\Controller\AuthController;
use app\Controller\UserController;

class Router
{
    public static function init(App $app): App
    {
        self::defineAuthRoutes($app);
        self::defineUserRoutes($app);
        self::defineCharacterRoutes($app);
        self::defineEquipmentRoutes($app);
        self::defineFactionRoutes($app);

        return $app;
    }

    private static function defineAuthRoutes(App $app)
    {
        $app->post('/login', [AuthController::class, 'login']);
    }

    private static function defineUserRoutes(App $app)
    {
        $app->post('/register', [UserController::class, 'register']);
        $app->get('/user', [UserController::class, 'getUser'])->add(new RoleMiddleware(1));;
    }
    private static function defineCharacterRoutes(App $app)
    {
        $app->group('/characters', function (RouteCollectorProxy $group) {
            $group->get('', 'app\Controller\CharacterController:index');
            $group->post('', 'app\Controller\CharacterController:store');
            $group->get('/{id}', 'app\Controller\CharacterController:show');
            $group->put('/{id}', 'app\Controller\CharacterController:update');
            $group->delete('/{id}', 'app\Controller\CharacterController:destroy');
        })->add(new RoleMiddleware(1));
    }

    private static function defineEquipmentRoutes(App $app)
    {
        $app->group('/equipment', function (RouteCollectorProxy $group) {
            $group->get('', 'app\Controller\EquipmentController:index');
            $group->post('', 'app\Controller\EquipmentController:store');
            $group->get('/{id}', 'app\Controller\EquipmentController:show');
            $group->put('/{id}', 'app\Controller\EquipmentController:update');
            $group->delete('/{id}', 'app\Controller\EquipmentController:destroy');
        })->add(new RoleMiddleware(2));;
    }

    private static function defineFactionRoutes(App $app)
    {
        $app->group('/factions', function (RouteCollectorProxy $group) {
            $group->get('', 'app\Controller\FactionController:index');
            $group->post('', 'app\Controller\FactionController:store');
            $group->get('/{id}', 'app\Controller\FactionController:show');
            $group->put('/{id}', 'app\Controller\FactionController:update');
            $group->delete('/{id}', 'app\Controller\FactionController:destroy');
        })->add(new RoleMiddleware(3));
    }
}
