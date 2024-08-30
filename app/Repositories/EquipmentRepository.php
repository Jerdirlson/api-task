<?php

namespace app\Repositories;

use app\Core\Database;
use PDO;
use PDOException;
use app\Utils\ErrorHandler;

class EquipmentRepository implements EquipmentRepositoryInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->db->query('SELECT * FROM equipments');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error fetching equipments', []);
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM equipments WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error fetching equipment', null);
        }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO equipments (name, type, made_by) VALUES (:name, :type, :made_by)');
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error creating equipment', false);
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $data['id'] = $id;
            $stmt = $this->db->prepare('UPDATE equipments SET name = :name, type = :type, made_by = :made_by WHERE id = :id');
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error updating equipment', false);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM equipments WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error deleting equipment', false);
        }
    }
}
