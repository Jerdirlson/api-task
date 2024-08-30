<?php

namespace app\Utils;

use Slim\Psr7\Response;

class ErrorHandler
{
    /**
     * Maneja los errores y devuelve una respuesta JSON estandarizada.
     *
     * @param Response $response
     * @param string $message
     * @param int $statusCode
     */
    public static function handle(Response $response, string $message, int $statusCode = 500): Response
    {
        $error = [
            'error' => [
                'message' => $message,
                'code' => $statusCode
            ]
        ];

        $response->getBody()->write(json_encode($error));
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
