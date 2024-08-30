<?php

namespace app\Model\Repositories;

use app\Core\Database;
use app\Model\Interfaces\FactionRepositoryInterface;
use app\Utils\ErrorHandler;
use PDOException;
use Predis\Client;

class FactionRepository implements FactionRepositoryInterface
{
    private $db;
    private $redis;

    public function __construct(Client $redis)
    {
        $this->db = Database::getConnection();
        $this->redis = $redis;
    }

    public function getAll(): array
    {
        try {
            $cacheKey = 'factions:all';
            $cachedData = $this->redis->get($cacheKey);

            if ($cachedData !== null) {
                return unserialize($cachedData);
            }

            $stmt = $this->db->query('SELECT * FROM factions');
            $data = $stmt->fetchAll();

            $this->redis->setex($cacheKey, 3600, serialize($data)); // Cache por 1 hora

            return $data;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error fetching factions', []);
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $cacheKey = "faction:$id";
            $cachedData = $this->redis->get($cacheKey);

            if ($cachedData !== null) {
                return unserialize($cachedData);
            }

            $stmt = $this->db->prepare('SELECT * FROM factions WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch() ?: null;

            if ($data) {
                $this->redis->setex($cacheKey, 3600, serialize($data)); // Cache por 1 hora
            }

            return $data;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error fetching faction', null);
        }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO factions (faction_name, description) VALUES (:faction_name, :description)');
            $result = $stmt->execute($data);

            if ($result) {
                // Limpiar el caché después de la creación
                $this->redis->del('factions:all');
            }

            return $result;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error creating faction', false);
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $data['id'] = $id;
            $stmt = $this->db->prepare('UPDATE factions SET faction_name = :faction_name, description = :description WHERE id = :id');
            $result = $stmt->execute($data);

            if ($result) {
                // Limpiar el caché después de la actualización
                $this->redis->del("faction:$id");
                $this->redis->del('factions:all');
            }

            return $result;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error updating faction', false);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM factions WHERE id = :id');
            $result = $stmt->execute(['id' => $id]);

            if ($result) {
                // Limpiar el caché después de la eliminación
                $this->redis->del("faction:$id");
                $this->redis->del('factions:all');
            }

            return $result;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error deleting faction', false);
        }
    }
}
