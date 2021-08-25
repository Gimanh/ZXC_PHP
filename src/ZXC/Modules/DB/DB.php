<?php

namespace ZXC\Modules\DB;

use PDO;
use PDOException;
use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;

class DB implements IModule
{
    use Module;

    /**
     * @var null | PDO
     */
    protected $pdo = null;
    /**
     * @var null | string
     */
    protected $errorCode = null;

    /**
     * @var null | string
     */
    protected $errorMessage = null;

    public function init(array $options = [])
    {
        $dsn = $options['dsn'] ?? null;
        $username = $options['username'] ?? null;
        $password = $options['password'] ?? null;
        $pdoOptions = $options['options'] ?? null;

        try {
            $this->pdo = new PDO($dsn, $username, $password, $pdoOptions);
        } catch (PDOException $e) {
            $this->errorCode = $e->getCode();
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * @return null|string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return PDO|null
     */
    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }
}
