<?php

namespace app\Repositories;

use app\Core\Database;
use PDO;
use PDOException;
use app\Utils\ErrorHandler;

class FactionRepository implements FactionRepositoryInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->db->query('SELECT * FROM factions');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error fetching factions', []);
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM factions WHERE id = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error fetching faction', null);
        }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO factions (faction_name, description) VALUES (:faction_name, :description)');
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error creating faction', false);
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $data['id'] = $id;
            $stmt = $this->db->prepare('UPDATE factions SET faction_name = :faction_name, description = :description WHERE id = :id');
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error updating faction', false);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM factions WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError($e, 'Error deleting faction', false);
        }
    }
}
