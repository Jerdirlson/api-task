<?php
namespace app\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

/**
 * @OA\Schema(
 *     schema="RoleMiddleware",
 *     description="Middleware to verify the user's role based on a JWT token.",
 *     type="object"
 * )
 */
class RoleMiddleware
{
    /**
     * @OA\Property(
     *     property="requiredRole",
     *     type="integer",
     *     description="The required role to access the protected route."
     * )
     *
     * @var int $requiredRole The role required for the route.
     */
    private $requiredRole;

    /**
     * Constructor to initialize the middleware with the required role.
     *
     * @param int $requiredRole The role needed to access the protected route.
     */
    public function __construct(int $requiredRole)
    {
        $this->requiredRole = $requiredRole;
    }

    /**
     * @OA\Method(
     *     method="__invoke",
     *     summary="Handles role verification using the JWT token",
     *     description="This method is automatically invoked by Slim to handle the user's role verification based on the JWT token provided in the authorization header.",
     *     parameters={
     *         @OA\Parameter(
     *             name="Authorization",
     *             in="header",
     *             required=true,
     *             description="JWT token in the format 'Bearer {token}'",
     *             @OA\Schema(type="string")
     *         )
     *     },
     *     responses={
     *         @OA\Response(
     *             response="200",
     *             description="Access granted"
     *         ),
     *         @OA\Response(
     *             response="401",
     *             description="Unauthorized",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="error", type="string", example="Authorization header not found")
     *             )
     *         ),
     *         @OA\Response(
     *             response="401",
     *             description="Invalid token or insufficient permissions",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="error", type="string", example="Invalid token")
     *             )
     *         )
     *     },
     *     throws="Exception"
     * )
     *
     * @param Request $request The HTTP request.
     * @param RequestHandler $handler The request handler.
     * @return Response The HTTP response.
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader) {
            return $this->unauthorizedResponse('Authorization header not found');
        }

        $authHeader = $authHeader[0];
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($_ENV['SECRET_KEY'], 'HS256'));

            $roles = $decoded->role;

            if ($this->requiredRole !== $roles) {
                return $this->unauthorizedResponse('Insufficient permissions');
            }

            $request = $request->withAttribute('user', $decoded);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Invalid token');
        }

        return $handler->handle($request);
    }

    /**
     * Generates an unauthorized (401) response.
     *
     * @param string $message The error message.
     * @return Response The HTTP response with an error message.
     */
    private function unauthorizedResponse(string $message = 'Unauthorized'): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
