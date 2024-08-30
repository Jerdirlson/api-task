<?php
namespace app\Controller;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use app\Model\Repositories\UserRepository;
use app\Utils\ResponseHelper;

class UserController
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Registra un nuevo usuario",
     *     description="Permite registrar un nuevo usuario en el sistema.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="newuser"),
     *             @OA\Property(property="password", type="string", example="securepassword"),
     *             @OA\Property(property="role", type="integer", example=3, description="ID del rol (opcional, por defecto 3)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User registered successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Nombre de usuario ya existe",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Username already exists")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al registrar el usuario",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to register user")
     *         )
     *     )
     * )
     */
    public function register(Request $request, Response $response): Response
    {
        $body = $request->getBody()->getContents();
        $params = json_decode($body, true);
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';
        $role = $params['role'] ?? 3;

        if ($this->userRepository->findByUsername($username)) {
            return ResponseHelper::error($response, 'Username already exists', 409);
        }

        if ($this->userRepository->create($username, $password, $role)) {
            return ResponseHelper::success($response, ['message' => 'User registered successfully'], 201);
        }

        return ResponseHelper::error($response, 'Failed to register user', 500);
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Obtiene los datos del usuario autenticado",
     *     description="Retorna la información del usuario autenticado actualmente.",
     *     @OA\Response(
     *         response=200,
     *         description="Datos del usuario obtenidos exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="currentuser"),
     *             @OA\Property(property="role", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function getUser(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        if ($user) {
            return ResponseHelper::success($response, $user);
        }

        return ResponseHelper::error($response, 'User not found', 404);
    }
}
