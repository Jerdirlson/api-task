<?php
namespace app\Utils;

use Slim\Psr7\Response;

class ResponseHelper
{
    /**
     * Devuelve una respuesta JSON para operaciones exitosas.
     *
     * @param Response $response
     * @param array $data
     * @param int $statusCode
     * @return Response
     */
    public static function success(Response $response, array $data = [], int $statusCode = 200): Response
    {
        $payload = [
            'status' => 'success',
            'data' => $data
        ];

        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    /**
     * Maneja los errores y devuelve una respuesta JSON estandarizada.
     *
     * @param Response $response
     * @param string $message
     * @param int $statusCode
     * @return Response
     */
    public static function error(Response $response, string $message, int $statusCode = 500): Response
    {
        $payload = [
            'status' => 'error',
            'error' => [
                'message' => $message,
                'code' => $statusCode
            ]
        ];

        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    /**
     * Maneja errores en el repositorio y registra logs.
     *
     * @param \Exception $e
     * @param string $defaultMessage
     * @param mixed $defaultReturn
     */
    public static function handleRepositoryError(\Exception $e, string $defaultMessage, $defaultReturn)
    {
        error_log($defaultMessage . ': ' . $e->getMessage());
        return $defaultReturn;
    }
}
