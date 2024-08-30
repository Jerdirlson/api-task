<?php

namespace app\Model\Interfaces;

interface UserRepositoryInterface
{
    /**
     * Encuentra un usuario por su nombre de usuario.
     *
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username): ?array;

    /**
     * Crea un nuevo usuario con una contraseña codificada.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function create(string $username, string $password): bool;
}
