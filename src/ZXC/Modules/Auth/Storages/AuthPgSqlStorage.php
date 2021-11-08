<?php

namespace ZXC\Modules\Auth\Storages;

use PDO;
use PDOException;
use ZXC\Modules\DB\DB;
use ZXC\Native\Modules;
use ZXC\Modules\Auth\AuthStorage;
use ZXC\Modules\Auth\Data\RegisterData;

class AuthPgSqlStorage implements AuthStorage
{
    /**
     * @var null | PDO
     */
    protected $pdo = null;

    protected $errorMessage = '';

    public function __construct()
    {
        /** @var DB $db */
        $db = Modules::get('db');
        if ($db) {
            $this->pdo = $db->getConnection();
        }
    }

    public function insetUser(RegisterData $registerData): int
    {
        $query = 'INSERT INTO tv_auth.users (login, email, password, confirm_email_code)
                    VALUES (:login, :email, :password, :confirm_email_code) RETURNING id;';
        $stmt = $this->pdo->prepare($query);

        try {
            $stmt->execute($registerData->getData());
        } catch (PDOException $exception) {
            $this->errorMessage = $exception->getMessage();
            return self::USER_NOT_INSERTED;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    public function fetchUserByLogin(string $login)
    {
        $query = 'SELECT * FROM tv_auth.users WHERE login = ?;';
        $stmt = $this->pdo->prepare($query);
        $status = $stmt->execute([$login]);
        if ($status) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function fetchUserByEmail(string $email)
    {
        $query = 'SELECT * FROM tv_auth.users WHERE email = ?;';
        $stmt = $this->pdo->prepare($query);
        $status = $stmt->execute([$email]);
        if ($status) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function fetchUserPermissions(int $userId): array
    {
        $query = 'SELECT DISTINCT p.*
                    FROM tv_auth.users usr
                             LEFT JOIN tv_auth.user_to_groups utg ON utg.user_id = usr.id
                             LEFT JOIN tv_auth.group_to_roles gtr ON gtr.group_id = utg.group_id
                             LEFT JOIN tv_auth.role_to_permissions rtp ON rtp.role_id = gtr.role_id
                             LEFT JOIN tv_auth.permissions p ON p.id = rtp.permission_id
                    WHERE utg.user_id = ?;';
        $stmt = $this->pdo->prepare($query);
        $status = $stmt->execute([$userId]);
        if ($status) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }
}
