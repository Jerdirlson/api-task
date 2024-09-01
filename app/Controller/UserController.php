<?php
namespace app\Controller;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use app\Model\Repositories\UserRepository;
use app\Utils\ResponseHelper;
use OpenApi\Attributes as OA;

class UserController
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    #[OA\Post(
        path: "/register",
        description: "Allows a new user to register in the system.",
        summary: "Register a new user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "password"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "newuser"),
                    new OA\Property(property: "password", type: "string", example: "securepassword"),
                    new OA\Property(property: "role", type: "integer", example: 3, description: "Role ID (optional, default 3)")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User registered successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User registered successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: "Username already exists",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Username already exists")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to register user",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Failed to register user")
                    ]
                )
            )
        ]
    )]
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

    #[OA\Get(
        path: "/user",
        description: "Returns the information of the currently authenticated user.",
        summary: "Get authenticated user data",
        responses: [
            new OA\Response(
                response: 200,
                description: "User data retrieved successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "username", type: "string", example: "currentuser"),
                        new OA\Property(property: "role", type: "integer", example: 3)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "User not found")
                    ]
                )
            )
        ]
    )]
    public function getUser(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        if ($user) {
            return ResponseHelper::success($response, $user);
        }

        return ResponseHelper::error($response, 'User not found', 404);
    }
}
