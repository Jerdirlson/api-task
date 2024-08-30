<?php

namespace app\Controller;

use app\Model\Repositories\UserRepository;
use Firebase\JWT\JWT;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use app\Utils\ResponseHelper;

class AuthController
{
    private $userModel;

    public function __construct(UserRepository $userModel)
    {
        $this->userModel = $userModel;
    }

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
            ];

            $token = JWT::encode($payload, 'your-secret-key', 'HS256');

            return ResponseHelper::success($response, ['token' => $token], 200);
        }

        return ResponseHelper::error($response, 'Invalid credentials', 401);
    }
}
