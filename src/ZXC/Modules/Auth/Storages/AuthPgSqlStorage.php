<?php

namespace ZXC\Modules\Auth\Storages;

use PDO;
use ZXC\Modules\Auth\AuthStorage;
use ZXC\Modules\Auth\Data\RegisterData;
use ZXC\Modules\DB\DB;
use ZXC\Native\Modules;

class AuthPgSqlStorage implements AuthStorage
{
    /**
     * @var null | PDO
     */
    protected $pdo = null;

    public function __construct()
    {
        /** @var DB $db */
        $db = Modules::get('db');
        if ($db) {
            $this->pdo = $db->getConnection();
        }
    }

    public function insetUser(RegisterData $registerData): bool
    {
        $query = '';
        $stop = false;
        return false;
    }

    public function fetchUser($login)
    {
        // TODO: Implement fetchUser() method.
    }
}
