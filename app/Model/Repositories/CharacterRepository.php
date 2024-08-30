<?php
namespace app\Model\Repositories;

use app\Core\Database;
use app\Model\Interfaces\CharacterRepositoryInterface;
use app\Utils\ErrorHandler;
use PDOException;
use Predis\Client;

class CharacterRepository implements CharacterRepositoryInterface
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
            $cacheKey = 'characters:all';
            $cachedData = $this->redis->get($cacheKey);

            if ($cachedData !== null) {
                return unserialize($cachedData);
            }

            $stmt = $this->db->query('SELECT * FROM characters');
            $data = $stmt->fetchAll();

            $this->redis->setex($cacheKey, 3600, serialize($data)); // Cache por 1 hora

            return $data;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError('Error fetching characters', $e->getMessage(), []);
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $cacheKey = "character:$id";
            $cachedData = $this->redis->get($cacheKey);

            if ($cachedData !== null) {
                return unserialize($cachedData);
            }

            $stmt = $this->db->prepare('SELECT * FROM characters WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch() ?: null;

            if ($data) {
                $this->redis->setex($cacheKey, 3600, serialize($data)); // Cache por 1 hora
            }

            return $data;
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
            $result = $stmt->execute($data);

            if ($result) {
                $this->redis->del('characters:all');
            }

            return $result;
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
            $result = $stmt->execute($data);

            if ($result) {
                $this->redis->del("character:$id");
                $this->redis->del('characters:all');
            }

            return $result;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError('Error updating character', $e->getMessage(), false);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM characters WHERE id = :id');
            $result = $stmt->execute(['id' => $id]);

            if ($result) {
                $this->redis->del("character:$id");
                $this->redis->del('characters:all');
            }

            return $result;
        } catch (PDOException $e) {
            return ErrorHandler::handleRepositoryError('Error deleting character', $e->getMessage(), false);
        }
    }
}
