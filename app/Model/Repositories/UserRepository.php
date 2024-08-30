<?php

namespace app\Model\Repositories;

use app\Core\Database;
use app\Model\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM `user` WHERE `username` = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);

        return $stmt->fetch() ?: null;
    }

    public function create(string $username, string $password, int $role = 3): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO `user` (`username`, `password`, `role_id`) VALUES (:username, :password, :role)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'username' => $username,
            'password' => $hashedPassword,
            'role' => $role,
        ]);
    }
}
