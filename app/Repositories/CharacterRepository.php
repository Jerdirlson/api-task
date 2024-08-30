<?php

namespace app\Repositories;

use app\Core\Database;
use app\Utils\ErrorHandler;
use PDOException;

class CharacterRepository implements CharacterRepositoryInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->db->query('SELECT * FROM characters');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError('Error fetching characters', $e->getMessage(), []);
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM characters WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError('Error fetching character', $e->getMessage(), null);
        }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO characters (name, birth_date, kingdom, equipment_id, faction_id)
                VALUES (:name, :birth_date, :kingdom, :equipment_id, :faction_id)
            ');
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError('Error creating character', $e->getMessage(), false);
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $data['id'] = $id;
            $stmt = $this->db->prepare('
                UPDATE characters
                SET name = :name, birth_date = :birth_date, kingdom = :kingdom, equipment_id = :equipment_id, faction_id = :faction_id
                WHERE id = :id
            ');
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError('Error updating character', $e->getMessage(), false);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM characters WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError('Error deleting character', $e->getMessage(), false);
        }
    }
}
