<?php

namespace app\Controller;

use app\Model\Interfaces\CharacterRepositoryInterface;
use app\Utils\ErrorHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Characters",
    description: "Endpoints relacionados con la gestión de personajes."
)]
class CharacterController
{
    private $repository;

    public function __construct(CharacterRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    #[OA\Get(
        path: "/characters",
        description: "Returns a list of all characters available in the database.",
        summary: "Get all characters",
        responses: [
            new OA\Response(
                response: 200,
                description: "Characters list retrieved successfully",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(type: "object", additionalProperties: true)
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to retrieve characters"
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
            return ErrorHandler::handle($response, 'Failed to fetch characters');
        }
    }

    #[OA\Get(
        path: "/characters/{id}",
        description: "Returns the details of a specific character based on its ID.",
        summary: "Get character by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Character ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Character retrieved successfully",
                content: new OA\JsonContent(type: "object", additionalProperties: true)
            ),
            new OA\Response(
                response: 404,
                description: "Character not found"
            ),
            new OA\Response(
                response: 500,
                description: "Failed to retrieve character"
            )
        ]
    )]
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->repository->getById((int) $args['id']);
            if ($data === null) {
                return ErrorHandler::handle($response, 'Character not found', 404);
            }

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to fetch character');
        }
    }

    #[OA\Post(
        path: "/characters",
        description: "Allows the creation of a new character in the database.",
        summary: "Create a new character",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: "object", additionalProperties: true)
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Character created successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Character created successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to create character"
            )
        ]
    )]
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            if ($this->repository->create($data)) {
                $response->getBody()->write(json_encode(['message' => 'Character created successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
            }
            return ErrorHandler::handle($response, 'Failed to create character');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to create character: ' . $e->getMessage());
        }
    }

    #[OA\Put(
        path: "/characters/{id}",
        description: "Allows updating the details of an existing character.",
        summary: "Update an existing character",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: "object", additionalProperties: true)
        ),
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Character ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Character updated successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Character updated successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to update character"
            )
        ]
    )]
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        try {
            if ($this->repository->update((int) $args['id'], $data)) {
                $response->getBody()->write(json_encode(['message' => 'Character updated successfully']));
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
            return ErrorHandler::handle($response, 'Failed to update character');
        } catch (\PDOException $e) {
            return ErrorHandler::handle($response, 'Failed to update character: ' . $e->getMessage());
        }
    }

    #[OA\Delete(
        path: "/characters/{id}",
        description: "Allows deleting a character from the database using its ID.",
        summary: "Delete a character by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Character ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Character deleted successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Character deleted successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Failed to delete character"
            )
        ]
    )]
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            if ($this->repository->delete((int) $args['id'])) {
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
