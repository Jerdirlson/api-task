<?php

namespace app\Model\Repositories;

use app\Core\Database;
use app\Model\Interfaces\EquipmentRepositoryInterface;
use app\Utils\ErrorHandler;
use PDOException;
use Predis\Client;

class EquipmentRepository implements EquipmentRepositoryInterface
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
            $cacheKey = 'equipments:all';
            $cachedData = $this->redis->get($cacheKey);

            if ($cachedData !== null) {
                return unserialize($cachedData);
            }

            $stmt = $this->db->query('SELECT * FROM equipments');
            $data = $stmt->fetchAll();

            $this->redis->setex($cacheKey, 3600, serialize($data)); // Cache por 1 hora

            return $data;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error fetching equipments', []);
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $cacheKey = "equipment:$id";
            $cachedData = $this->redis->get($cacheKey);

            if ($cachedData !== null) {
                return unserialize($cachedData);
            }

            $stmt = $this->db->prepare('SELECT * FROM equipments WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch() ?: null;

            if ($data) {
                $this->redis->setex($cacheKey, 3600, serialize($data)); // Cache por 1 hora
            }

            return $data;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error fetching equipment', null);
        }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO equipments (name, type, made_by) VALUES (:name, :type, :made_by)');
            $result = $stmt->execute($data);

            if ($result) {
                // Limpiar el caché después de la creación
                $this->redis->del('equipments:all');
            }

            return $result;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error creating equipment', false);
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $data['id'] = $id;
            $stmt = $this->db->prepare('UPDATE equipments SET name = :name, type = :type, made_by = :made_by WHERE id = :id');
            $result = $stmt->execute($data);

            if ($result) {
                // Limpiar el caché después de la actualización
                $this->redis->del("equipment:$id");
                $this->redis->del('equipments:all');
            }

            return $result;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error updating equipment', false);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM equipments WHERE id = :id');
            $result = $stmt->execute(['id' => $id]);

            if ($result) {
                // Limpiar el caché después de la eliminación
                $this->redis->del("equipment:$id");
                $this->redis->del('equipments:all');
            }

            return $result;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error deleting equipment', false);
        }
    }
}
