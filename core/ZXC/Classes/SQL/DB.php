<?php

namespace ZXC\Classes\SQL;

class DB
{
    /**
     * @var \PDO
     */
    private $pdo;
    private $dsn;
    private $dbName;
    private $user;
    private $password;
    private $charset = 'utf8';
    private $dbType;
    private $dbHost;
    private $port;
    private $errorCode;
    private $errorMessage;
    private $fetchStyle = \PDO::FETCH_OBJ;

    public function initialize(array $config = [])
    {
        if (!$this->checkConfig($config)) {
            throw new \InvalidArgumentException('Invalid config for DB connection');
        }
        $this->setVarsFromConfig($config);
        $this->createPDOInstance();
    }

    public function createPDOInstance()
    {
        try {
            $this->pdo = new \PDO($this->dsn, $this->user, $this->password);
        } catch (\PDOException $e) {
            $this->errorCode = $e->getCode();
            $this->errorMessage = $e->getMessage();
        }
    }

    public function setVarsFromConfig($config)
    {
        if (!$config) {
            return false;
        }
        $this->dbName = $config['dbname'];
        $this->dbType = $config['dbtype'];
        $this->port = $config['port'];
        $this->dbHost = $config['host'];
        $this->password = $config['password'];
        $this->user = $config['user'];
        if (isset($config['charset'])) {
            $this->charset = $config['charset'];
        }
        $this->dsn = $this->dbType . ':dbname=' . $this->dbName . ';host=' . $this->dbHost . ';port=' . $this->port;
        return true;
    }

    public function checkConfig(array $config = [])
    {
        return isset($config['dbname']) && isset($config['dbtype'])
            && isset($config['host']) && isset($config['port'])
            && isset($config['password']) && isset($config['user']);
    }

    public function exec($query, array $params = [])
    {
        try {
            $resultArr = [];
            $this->begin();
            $state = $this->pdo->prepare($query);
            $result = $state->execute($params);
            $this->commit();
            if ($result) {
                $resultArr = $state->fetchAll($this->fetchStyle);
            }
            return $resultArr;
        } catch (\Exception $e) {
            $this->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    public function begin()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}