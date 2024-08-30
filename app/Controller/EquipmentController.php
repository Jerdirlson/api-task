<?php
namespace app\Controller;

use app\Model\Interfaces\EquipmentRepositoryInterface;
use app\Utils\ErrorHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class EquipmentController
{
    private $repository;

    public function __construct(EquipmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *     path="/equipment",
     *     summary="Obtiene una lista de todo el equipo",
     *     description="Retorna una lista con todos los equipos disponibles en la base de datos.",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de equipos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Equipment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener los equipos"
     *     )
     * )
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $data = $this->repository->getAll();

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to fetch equipment');
        }
    }

    /**
     * @OA\Get(
     *     path="/equipment/{id}",
     *     summary="Obtiene un equipo por ID",
     *     description="Retorna los datos de un equipo específico basado en su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del equipo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipo encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Equipment")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipo no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener el equipo"
     *     )
     * )
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->repository->getById((int) $args['id']);
            if ($data === null) {
                return ErrorHandler::handle($response, 'Equipment not found', 404);
            }

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to fetch equipment');
        }
    }

    /**
     * @OA\Post(
     *     path="/equipment",
     *     summary="Crea un nuevo equipo",
     *     description="Permite crear un nuevo equipo en la base de datos.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Equipment")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Equipo creado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Equipment created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al crear el equipo"
     *     )
     * )
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            if ($this->repository->create($data)) {
                $response->getBody()->write(json_encode(['message' => 'Equipment created successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
            }
            return ErrorHandler::handle($response, 'Failed to create equipment');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to create equipment: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/equipment/{id}",
     *     summary="Actualiza un equipo existente",
     *     description="Permite actualizar los datos de un equipo existente.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del equipo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Equipment")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipo actualizado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Equipment updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al actualizar el equipo"
     *     )
     * )
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        try {
            if ($this->repository->update((int) $args['id'], $data)) {
                $response->getBody()->write(json_encode(['message' => 'Equipment updated successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
            return ErrorHandler::handle($response, 'Failed to update equipment');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to update equipment: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/equipment/{id}",
     *     summary="Elimina un equipo por ID",
     *     description="Permite eliminar un equipo de la base de datos usando su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del equipo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipo eliminado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Equipment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al eliminar el equipo"
     *     )
     * )
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            if ($this->repository->delete((int) $args['id'])) {
                $response->getBody()->write(json_encode(['message' => 'Equipment deleted successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
            return ErrorHandler::handle($response, 'Failed to delete equipment');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to delete equipment: ' . $e->getMessage());
        }
    }
}
