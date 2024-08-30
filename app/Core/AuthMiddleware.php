<?php
namespace app\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader) {
            return $this->unauthorizedResponse();
        }

        $authHeader = $authHeader[0];
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key('your-secret-key', 'HS256'));
            $request = $request->withAttribute('user', $decoded);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Invalid token');
        }

        return $handler->handle($request);
    }

    private function unauthorizedResponse(string $message = 'Unauthorized'): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
