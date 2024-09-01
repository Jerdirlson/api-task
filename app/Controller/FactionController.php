<?php
namespace app\Controller;

use app\Model\Interfaces\FactionRepositoryInterface;
use app\Utils\ErrorHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use OpenApi\Attributes as OA;

class FactionController
{
    private $repository;

    public function __construct(FactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    #[OA\Get(
        path: "/factions",
        description: "Returns a list of all factions available in the database.",
        summary: "Get all factions",
        responses: [
            new OA\Response(
                response: 200,
                description: "Factions list retrieved successfully",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(type: "object", additionalProperties: true)
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to retrieve factions"
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
            return ErrorHandler::handle($response, 'Failed to fetch factions');
        }
    }

    #[OA\Get(
        path: "/factions/{id}",
        description: "Returns the details of a specific faction based on its ID.",
        summary: "Get faction by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Faction ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Faction retrieved successfully",
                content: new OA\JsonContent(type: "object", additionalProperties: true)
            ),
            new OA\Response(
                response: 404,
                description: "Faction not found"
            ),
            new OA\Response(
                response: 500,
                description: "Failed to retrieve faction"
            )
        ]
    )]
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

    #[OA\Post(
        path: "/factions",
        description: "Allows the creation of a new faction in the database.",
        summary: "Create new faction",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: "object", additionalProperties: true)
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Faction created successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Faction created successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to create faction"
            )
        ]
    )]
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

    #[OA\Put(
        path: "/factions/{id}",
        description: "Allows updating the details of an existing faction.",
        summary: "Update existing faction",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: "object", additionalProperties: true)
        ),
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Faction ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Faction updated successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Faction updated successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to update faction"
            )
        ]
    )]
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

    #[OA\Delete(
        path: "/factions/{id}",
        description: "Allows deleting a faction from the database using its ID.",
        summary: "Delete faction by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Faction ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Faction deleted successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Faction deleted successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to delete faction"
            )
        ]
    )]
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
