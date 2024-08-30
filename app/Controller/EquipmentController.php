<?php
namespace app\Controller;

use app\Repositories\EquipmentRepositoryInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use app\Utils\ErrorHandler;

class EquipmentController
{
    private $repository;

    public function __construct(EquipmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

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
