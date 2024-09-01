<?php
namespace app\Controller;

use app\Model\Repositories\UserRepository;
use Firebase\JWT\JWT;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use app\Utils\ResponseHelper;
use OpenApi\Attributes as OA;

#[OA\Info(version: "0.1", title: "Yeye API")]

#[OA\Tag(
    name: "Auth",
    description: "Endpoints relacionados con la autenticación de usuarios."
)]
class AuthController
{
    private $userModel;

    public function __construct(UserRepository $userModel)
    {
        $this->userModel = $userModel;
    }

    #[OA\Post(
        path: "/login",
        summary: "Inicia sesión y obtiene un token JWT",
        description: "Este endpoint permite a un usuario autenticarse y recibir un token JWT que puede ser utilizado para acceder a rutas protegidas.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "password"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Inicio de sesión exitoso, retorna el token JWT",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Credenciales inválidas",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Invalid credentials")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Solicitud incorrecta",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Bad request")
                    ]
                )
            )
        ],
        security: []
    )]
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
