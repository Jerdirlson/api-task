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
 *     description="Middleware para verificar el rol del usuario basado en un token JWT.",
 *     type="object"
 * )
 */
class RoleMiddleware
{
    /**
     * @OA\Property(
     *     property="requiredRole",
     *     type="integer",
     *     description="El rol requerido para acceder a la ruta protegida."
     * )
     *
     * @var int $requiredRole El rol requerido para la ruta.
     */
    private $requiredRole;

    /**
     * Constructor para inicializar el middleware con el rol requerido.
     *
     * @param int $requiredRole El rol necesario para acceder a la ruta protegida.
     */
    public function __construct(int $requiredRole)
    {
        $this->requiredRole = $requiredRole;
    }

    /**
     * @OA\Method(
     *     method="__invoke",
     *     summary="Maneja la verificación del rol a través del token JWT",
     *     description="Este método es invocado automáticamente por Slim para manejar la verificación del rol del usuario basado en el token JWT proporcionado en el encabezado de autorización.",
     *     parameters={
     *         @OA\Parameter(
     *             name="Authorization",
     *             in="header",
     *             required=true,
     *             description="Token JWT en el formato 'Bearer {token}'",
     *             @OA\Schema(type="string")
     *         )
     *     },
     *     responses={
     *         @OA\Response(
     *             response="200",
     *             description="Acceso permitido"
     *         ),
     *         @OA\Response(
     *             response="401",
     *             description="No autorizado",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="error", type="string", example="Authorization header not found")
     *             )
     *         ),
     *         @OA\Response(
     *             response="401",
     *             description="Token inválido o permisos insuficientes",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="error", type="string", example="Invalid token")
     *             )
     *         )
     *     },
     *     throws="Exception"
     * )
     *
     * @param Request $request La solicitud HTTP.
     * @param RequestHandler $handler El manejador de la solicitud.
     * @return Response La respuesta HTTP.
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
     * Genera una respuesta de no autorizado (401).
     *
     * @param string $message El mensaje de error.
     * @return Response La respuesta HTTP con un mensaje de error.
     */
    private function unauthorizedResponse(string $message = 'Unauthorized'): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
