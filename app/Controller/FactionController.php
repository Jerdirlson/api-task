<?php
namespace app\Controller;

use app\Model\Interfaces\FactionRepositoryInterface;
use app\Utils\ErrorHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class FactionController
{
    private $repository;

    public function __construct(FactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *     path="/factions",
     *     summary="Obtiene una lista de todas las facciones",
     *     description="Retorna una lista con todas las facciones disponibles en la base de datos.",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de facciones obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Faction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener las facciones"
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
            return ErrorHandler::handle($response, 'Failed to fetch factions');
        }
    }

    /**
     * @OA\Get(
     *     path="/factions/{id}",
     *     summary="Obtiene una facción por ID",
     *     description="Retorna los datos de una facción específica basada en su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la facción",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facción encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Faction")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facción no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener la facción"
     *     )
     * )
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->repository->getById((int) $args['id']);
            if ($data === null) {
                return ErrorHandler::handle($response, 'Faction not found', 404);
            }

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to fetch faction');
        }
    }

    /**
     * @OA\Post(
     *     path="/factions",
     *     summary="Crea una nueva facción",
     *     description="Permite crear una nueva facción en la base de datos.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Faction")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Facción creada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Faction created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al crear la facción"
     *     )
     * )
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            if ($this->repository->create($data)) {
                $response->getBody()->write(json_encode(['message' => 'Faction created successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
            }
            return ErrorHandler::handle($response, 'Failed to create faction');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to create faction: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/factions/{id}",
     *     summary="Actualiza una facción existente",
     *     description="Permite actualizar los datos de una facción existente.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la facción",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Faction")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facción actualizada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Faction updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al actualizar la facción"
     *     )
     * )
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        try {
            if ($this->repository->update((int) $args['id'], $data)) {
                $response->getBody()->write(json_encode(['message' => 'Faction updated successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
            return ErrorHandler::handle($response, 'Failed to update faction');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to update faction: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/factions/{id}",
     *     summary="Elimina una facción por ID",
     *     description="Permite eliminar una facción de la base de datos usando su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la facción",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facción eliminada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Faction deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al eliminar la facción"
     *     )
     * )
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            if ($this->repository->delete((int) $args['id'])) {
                $response->getBody()->write(json_encode(['message' => 'Faction deleted successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
            return ErrorHandler::handle($response, 'Failed to delete faction');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to delete faction: ' . $e->getMessage());
        }
    }
}
