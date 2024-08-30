<?php
namespace app\Core;

use PDO;
use PDOException;

/**
 * @OA\Schema(
 *     schema="Database",
 *     description="Clase encargada de gestionar la conexión a la base de datos utilizando PDO.",
 *     type="object"
 * )
 */
class Database
{
    /**
     * @var PDO|null $connection La instancia única de conexión a la base de datos.
     */
    private static $connection;

    /**
     * @OA\Method(
     *     method="getConnection",
     *     summary="Obtiene la instancia de conexión a la base de datos",
     *     description="Este método sigue el patrón Singleton para asegurar que solo exista una instancia de conexión a la base de datos durante la ejecución del script. Utiliza PDO para conectarse a la base de datos definida en las variables de entorno.",
     *     responses={
     *         @OA\Response(
     *             response="200",
     *             description="Conexión a la base de datos establecida exitosamente.",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="connection", ref="#/components/schemas/PDO")
     *             )
     *         ),
     *         @OA\Response(
     *             response="500",
     *             description="Error al intentar establecer la conexión a la base de datos.",
     *             @OA\Schema(
     *                 type="string",
     *                 example="Error de conexión: Detalles del error"
     *             )
     *         )
     *     },
     *     throws="PDOException"
     * )
     *
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Validar variables de entorno
            $requiredEnv = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
            foreach ($requiredEnv as $env) {
                if (empty($_ENV[$env])) {
                    throw new PDOException("Falta la variable de entorno: $env");
                }
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
                $_ENV['DB_HOST'],
                $_ENV['DB_PORT'],
                $_ENV['DB_NAME']
            );

            try {
                self::$connection = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException('Error de conexión: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
