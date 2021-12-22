<?php

namespace ZXC\Modules\Auth;

use ZXC\Modules\Auth\Data\RegisterData;

interface AuthStorage
{
    const USER_NOT_INSERTED = -1;

    public function fetchUserByLogin(string $login);

    public function fetchUserByEmail(string $email);

    public function fetchUserPermissions(int $userId): array;

    /**
     * Add user to database
     * @param RegisterData $registerData
     * @return int -1 or inserted user id
     */
    public function insertUser(RegisterData $registerData): int;

    public function fetchUserById(int $id);
}
