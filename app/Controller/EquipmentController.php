<?php
namespace app\Controller;

use app\Model\Interfaces\EquipmentRepositoryInterface;
use app\Utils\ErrorHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use OpenApi\Attributes as OA;

class EquipmentController
{
    private $repository;

    public function __construct(EquipmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    #[OA\Get(
        path: "/equipment",
        description: "Returns a list of all equipment available in the database.",
        summary: "Get all equipment",
        responses: [
            new OA\Response(
                response: 200,
                description: "Equipment list retrieved successfully",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(type: "object", additionalProperties: true)
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to retrieve equipment"
            )
        ]
    )]
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

    #[OA\Get(
        path: "/equipment/{id}",
        description: "Returns the details of specific equipment based on its ID.",
        summary: "Get equipment by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Equipment ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Equipment retrieved successfully",
                content: new OA\JsonContent(type: "object", additionalProperties: true)
            ),
            new OA\Response(
                response: 404,
                description: "Equipment not found"
            ),
            new OA\Response(
                response: 500,
                description: "Failed to retrieve equipment"
            )
        ]
    )]
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

    #[OA\Post(
        path: "/equipment",
        description: "Allows the creation of new equipment in the database.",
        summary: "Create new equipment",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: "object", additionalProperties: true)
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Equipment created successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Equipment created successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to create equipment"
            )
        ]
    )]
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

    #[OA\Put(
        path: "/equipment/{id}",
        description: "Allows updating the details of existing equipment.",
        summary: "Update existing equipment",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: "object", additionalProperties: true)
        ),
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Equipment ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Equipment updated successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Equipment updated successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to update equipment"
            )
        ]
    )]
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

    #[OA\Delete(
        path: "/equipment/{id}",
        description: "Allows deleting equipment from the database using its ID.",
        summary: "Delete equipment by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Equipment ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Equipment deleted successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Equipment deleted successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to delete equipment"
            )
        ]
    )]
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
