<?php
namespace app\Controller;

use Firebase\JWT\JWT;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AuthController
{
    public function login(Request $request, Response $response): Response
    {
        $body = $request->getBody()->getContents();
        $params = json_decode($body, true);
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';

        error_log("Username: $username, Password: $password");

        // Aquí deberías validar el usuario contra tu base de datos
        if ($username === 'test' && $password === 'password') {
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600; // JWT válido por 1 hora
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'username' => $username,
            ];

            $token = JWT::encode($payload, 'your-secret-key', 'HS256');
            $response->getBody()->write(json_encode(['token' => $token]));

            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }

        $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
