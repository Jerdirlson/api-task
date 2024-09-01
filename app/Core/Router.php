<?php

namespace app\Core;

use Predis\Client;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use app\Core\RoleMiddleware;
use app\Controller\AuthController;
use app\Controller\UserController;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**

 *
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="Servidor local"
 * )
 */
class Router
{
    /**
     * Inicializa las rutas de la aplicación.
     *
     * @param App $app
     * @return App
     */
    public static function init(App $app): App
    {
        self::defineAuthRoutes($app);
        self::defineUserRoutes($app);
        self::defineCharacterRoutes($app);
        self::defineEquipmentRoutes($app);
        self::defineFactionRoutes($app);

        /**
         * @OA\Get(
         *     path="/docs",
         *     summary="Swagger UI",
         *     @OA\Response(response=200, description="Swagger UI")
         * )
         */
        $app->get('/docs', function (Request $request, Response $response) {
            $filePath = __DIR__ . '/../Documentation/index.html';
            $response->getBody()->write(file_get_contents($filePath));
            return $response->withHeader('Content-Type', 'text/html');
        });

        return $app;
    }

    /**
     * Define las rutas de autenticación.
     *
     * @param App $app
     */
    private static function defineAuthRoutes(App $app)
    {
        /**
         * @OA\Post(
         *     path="/login",
         *     summary="Login",
         *     @OA\RequestBody(
         *         @OA\JsonContent(
         *             @OA\Property(property="username", type="string"),
         *             @OA\Property(property="password", type="string")
         *         )
         *     ),
         *     @OA\Response(response=200, description="JWT Token"),
         *     @OA\Response(response=401, description="Invalid credentials")
         * )
         */
        $app->post('/login', [AuthController::class, 'login']);
    }

    /**
     * Define las rutas de usuarios.
     *
     * @param App $app
     */
    private static function defineUserRoutes(App $app)
    {
        /**
         * @OA\Post(
         *     path="/register",
         *     summary="Register",
         *     @OA\RequestBody(
         *         @OA\JsonContent(
         *             @OA\Property(property="username", type="string"),
         *             @OA\Property(property="password", type="string"),
         *             @OA\Property(property="role", type="integer", example=3)
         *         )
         *     ),
         *     @OA\Response(response=201, description="User registered"),
         *     @OA\Response(response=409, description="Username already exists")
         * )
         */
        $app->post('/register', [UserController::class, 'register']);

        /**
         * @OA\Get(
         *     path="/user",
         *     summary="Get User",
         *     @OA\Response(response=200, description="User details"),
         *     @OA\Response(response=404, description="User not found")
         * )
         */
        $app->get('/user', [UserController::class, 'getUser'])->add(new RoleMiddleware(1));
    }

    /**
     * Define las rutas de personajes.
     *
     * @param App $app
     */
    private static function defineCharacterRoutes(App $app)
    {
        /**
         * @OA\Get(
         *     path="/characters",
         *     summary="Get Characters",
         *     @OA\Response(response=200, description="List of characters")
         * )
         * @OA\Post(
         *     path="/characters",
         *     summary="Create Character",
         *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Character")),
         *     @OA\Response(response=201, description="Character created")
         * )
         */
        $app->group('/characters', function (RouteCollectorProxy $group) {
            $group->get('', 'app\Controller\CharacterController:index');
            $group->post('', 'app\Controller\CharacterController:store');

            /**
             * @OA\Get(
             *     path="/characters/{id}",
             *     summary="Get Character",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\Response(response=200, description="Character details"),
             *     @OA\Response(response=404, description="Character not found")
             * )
             * @OA\Put(
             *     path="/characters/{id}",
             *     summary="Update Character",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Character")),
             *     @OA\Response(response=200, description="Character updated")
             * )
             * @OA\Delete(
             *     path="/characters/{id}",
             *     summary="Delete Character",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\Response(response=200, description="Character deleted")
             * )
             */
            $group->get('/{id}', 'app\Controller\CharacterController:show');
            $group->put('/{id}', 'app\Controller\CharacterController:update');
            $group->delete('/{id}', 'app\Controller\CharacterController:destroy');
        })->add(new RoleMiddleware(1));
    }

    /**
     * Define las rutas de equipos.
     *
     * @param App $app
     */
    private static function defineEquipmentRoutes(App $app)
    {
        /**
         * @OA\Get(
         *     path="/equipment",
         *     summary="Get Equipment",
         *     @OA\Response(response=200, description="List of equipment")
         * )
         * @OA\Post(
         *     path="/equipment",
         *     summary="Create Equipment",
         *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Equipment")),
         *     @OA\Response(response=201, description="Equipment created")
         * )
         */
        $app->group('/equipment', function (RouteCollectorProxy $group) {
            $group->get('', 'app\Controller\EquipmentController:index');
            $group->post('', 'app\Controller\EquipmentController:store');

            /**
             * @OA\Get(
             *     path="/equipment/{id}",
             *     summary="Get Equipment",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\Response(response=200, description="Equipment details"),
             *     @OA\Response(response=404, description="Equipment not found")
             * )
             * @OA\Put(
             *     path="/equipment/{id}",
             *     summary="Update Equipment",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Equipment")),
             *     @OA\Response(response=200, description="Equipment updated")
             * )
             * @OA\Delete(
             *     path="/equipment/{id}",
             *     summary="Delete Equipment",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\Response(response=200, description="Equipment deleted")
             * )
             */
            $group->get('/{id}', 'app\Controller\EquipmentController:show');
            $group->put('/{id}', 'app\Controller\EquipmentController:update');
            $group->delete('/{id}', 'app\Controller\EquipmentController:destroy');
        })->add(new RoleMiddleware(2));
    }

    /**
     * Define las rutas de facciones.
     *
     * @param App $app
     */
    private static function defineFactionRoutes(App $app)
    {
        /**
         * @OA\Get(
         *     path="/factions",
         *     summary="Get Factions",
         *     @OA\Response(response=200, description="List of factions")
         * )
         * @OA\Post(
         *     path="/factions",
         *     summary="Create Faction",
         *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Faction")),
         *     @OA\Response(response=201, description="Faction created")
         * )
         */
        $app->group('/factions', function (RouteCollectorProxy $group) {
            $group->get('', 'app\Controller\FactionController:index');
            $group->post('', 'app\Controller\FactionController:store');

            /**
             * @OA\Get(
             *     path="/factions/{id}",
             *     summary="Get Faction",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\Response(response=200, description="Faction details"),
             *     @OA\Response(response=404, description="Faction not found")
             * )
             * @OA\Put(
             *     path="/factions/{id}",
             *     summary="Update Faction",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Faction")),
             *     @OA\Response(response=200, description="Faction updated")
             * )
             * @OA\Delete(
             *     path="/factions/{id}",
             *     summary="Delete Faction",
             *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
             *     @OA\Response(response=200, description="Faction deleted")
             * )
             */
            $group->get('/{id}', 'app\Controller\FactionController:show');
            $group->put('/{id}', 'app\Controller\FactionController:update');
            $group->delete('/{id}', 'app\Controller\FactionController:destroy');
        })->add(new RoleMiddleware(3));
    }
}
