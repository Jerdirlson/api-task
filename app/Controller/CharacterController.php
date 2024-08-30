<?php
namespace app\Controller;

use app\Model\Interfaces\CharacterRepositoryInterface;
use app\Utils\ErrorHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class CharacterController
{
    private $repository;

    public function __construct(CharacterRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *     path="/characters",
     *     summary="Obtiene una lista de todos los personajes",
     *     description="Retorna una lista con todos los personajes disponibles en la base de datos.",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de personajes obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Character")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener los personajes"
     *     )
     * )
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $data = $this->repository->getAll();

            // Convertir a JSON y escribir en el cuerpo de la respuesta
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to fetch characters');
        }
    }

    /**
     * @OA\Get(
     *     path="/characters/{id}",
     *     summary="Obtiene un personaje por ID",
     *     description="Retorna los datos de un personaje específico basado en su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del personaje",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personaje encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Character")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Personaje no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener el personaje"
     *     )
     * )
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->repository->getById((int) $args['id']);
            if ($data === null) {
                return ErrorHandler::handle($response, 'Character not found', 404);
            }

            // Convertir a JSON y escribir en el cuerpo de la respuesta
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to fetch character');
        }
    }

    /**
     * @OA\Post(
     *     path="/characters",
     *     summary="Crea un nuevo personaje",
     *     description="Permite crear un nuevo personaje en la base de datos.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Character")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Personaje creado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Character created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al crear el personaje"
     *     )
     * )
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            if ($this->repository->create($data)) {
                // Mensaje de éxito en JSON
                $response->getBody()->write(json_encode(['message' => 'Character created successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
            }
            return ErrorHandler::handle($response, 'Failed to create character');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to create character: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/characters/{id}",
     *     summary="Actualiza un personaje existente",
     *     description="Permite actualizar los datos de un personaje existente.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del personaje",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Character")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personaje actualizado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Character updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al actualizar el personaje"
     *     )
     * )
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        try {
            if ($this->repository->update((int) $args['id'], $data)) {
                // Mensaje de éxito en JSON
                $response->getBody()->write(json_encode(['message' => 'Character updated successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
            return ErrorHandler::handle($response, 'Failed to update character');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to update character: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/characters/{id}",
     *     summary="Elimina un personaje por ID",
     *     description="Permite eliminar un personaje de la base de datos usando su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del personaje",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personaje eliminado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Character deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al eliminar el personaje"
     *     )
     * )
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            if ($this->repository->delete((int) $args['id'])) {
                // Mensaje de éxito en JSON
                $response->getBody()->write(json_encode(['message' => 'Character deleted successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
            return ErrorHandler::handle($response, 'Failed to delete character');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to delete character: ' . $e->getMessage());
        }
    }
}
