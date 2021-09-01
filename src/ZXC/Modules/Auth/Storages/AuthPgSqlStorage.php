<?php

namespace ZXC\Modules\Auth\Storages;

use Exception;
use PDO;
use PDOException;
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

        if (is_callable('ZXC\Modules\Auth\Handlers\AuthenticationRegistration')) {

        }
        try {
            $stmt->execute($registerData->getData());
        } catch (PDOException $exception) {
            $this->errorMessage = $exception->getMessage();
            return -1;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    public function fetchUser($login)
    {
        // TODO: Implement fetchUser() method.
    }
}
