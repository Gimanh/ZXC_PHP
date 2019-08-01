<?php

namespace ZXC\Native;

use PDO;
use Exception;
use PDOException;
use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;
use InvalidArgumentException;
use ZXC\Interfaces\Native\IDB;

class DB implements IDB, IModule
{
    use Module;

    protected $version = '0.0.1';
    /**
     * @var PDO
     */
    protected $pdo = null;
    protected $dsn = null;
    protected $dbName = null;
    protected $user = null;
    protected $password = null;
    protected $charset = 'utf8';
    protected $dbType = null;
    protected $dbHost = null;
    protected $port = null;
    protected $errorCode = null;
    protected $errorMessage = null;
    protected $fetchStyle = PDO::FETCH_ASSOC;

    public function initialize(array $config = null)
    {
        if (!$this->checkConfig($config)) {
            throw new InvalidArgumentException('Invalid config for DB connection');
        }
        $this->setVarsFromConfig($config);
        $this->createPDOInstance();
        return true;
    }

    public function createPDOInstance()
    {
        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password);
        } catch (PDOException $e) {
            $this->errorCode = $e->getCode();
            $this->errorMessage = $e->getMessage();
            throw new InvalidArgumentException('PDO create error check error code');
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
            if (!$result) {
                $this->rollBack();
                $this->errorMessage = $state->errorInfo()[2];
                return false;
            }
            $this->commit();
            if ($result) {
                $resultArr = $state->fetchAll($this->fetchStyle);
            }
            return $resultArr;
        } catch (Exception $e) {
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

    public function lastInsertId($seq)
    {
        return $this->pdo->lastInsertId($seq);
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * @return null
     */
    public function getDbType()
    {
        return $this->dbType;
    }

    /**
     * @return null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}