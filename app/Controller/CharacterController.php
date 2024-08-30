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
