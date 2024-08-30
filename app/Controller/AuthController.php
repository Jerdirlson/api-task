<?php
namespace app\Controller;

use app\Model\Repositories\UserRepository;
use Firebase\JWT\JWT;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use app\Utils\ResponseHelper;


/**
 * @OA\Info(
 *     title="API de Ejemplo",
 *     version="1.0.0",
 *     description="Documentación de la API para autenticación y manejo de usuarios."
 * )
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoints relacionados con la autenticación de usuarios."
 * )
 */
class AuthController
{
    private $userModel;

    public function __construct(UserRepository $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Inicia sesión y obtiene un token JWT",
     *     description="Este endpoint permite a un usuario autenticarse y recibir un token JWT que puede ser utilizado para acceder a rutas protegidas.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso, retorna el token JWT",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales inválidas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solicitud incorrecta",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Bad request")
     *         )
     *     ),
     *     security={}
     * )
     */
    public function login(Request $request, Response $response): Response
    {
        $body = $request->getBody()->getContents();
        $params = json_decode($body, true);
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600;
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'username' => $username,
                'role' => $user['role_id'],
            ];

            $token = JWT::encode($payload, $_ENV['SECRET_KEY'], 'HS256');

            return ResponseHelper::success($response, ['token' => $token], 200);
        }

        return ResponseHelper::error($response, 'Invalid credentials', 401);
    }
}
