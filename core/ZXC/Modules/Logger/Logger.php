<?php

namespace ZXC\Modules\Logger;

use ZXC\ZXC;
use DateTime;
use Exception;
use ZXC\Native\Helper;
use ZXC\Traits\Module;
use Psr\Log\AbstractLogger;
use ZXC\Interfaces\IModule;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface, IModule
{
    use Module;

    protected $version = '0.0.1';
    private $root = null;
    private $dateFormat = DateTime::RFC2822;
    private $logFileName = null;
    private $template = "{date} | {level} | {ip} | {message} | {context}";
    private $level = null;
    private $logsFolder = null;
    private $fullLogFilePath = null;

    public function __construct(array $config = [])
    {
        $this->initialize($config);
    }

    public function initialize(array $config = null)
    {
        if (!$config) {
            return false;
        }
        $this->level = isset($config['applevel']) ? $config['applevel'] : 'production';

        if (isset($config['folder'])) {
            if (isset($config['file'])) {
                $this->logFileName = $config['file'];
            } else {
                $this->logFileName = 'zxc.log';
            }

            $this->logsFolder = $config['folder'];

            if (isset($config['root'])) {
                $this->root = $config['root'];
            }
            $this->updateFullLogFilePath();
            if (!file_exists($this->fullLogFilePath)) {
                touch($this->fullLogFilePath);
            }
            if (isset($config['template'])) {
                $this->template = $config['template'];
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function log($level, $message, array $context = [])
    {
        $this->updateFullLogFilePath();
        $ip = ZXC::getIp();
        file_put_contents($this->fullLogFilePath, trim(strtr($this->template, [
                '{date}' => $this->getDate(),
                '{level}' => $level,
                '{ip}' => $ip,
                '{message}' => $message,
                '{context}' => $this->contextStringify($context),
            ])) . PHP_EOL, FILE_APPEND);
    }

    public function updateFullLogFilePath()
    {
        $lastSlash = substr($this->logsFolder, -1);
        if ($lastSlash === '/') {
            $this->logsFolder = rtrim($this->logsFolder, '/');
        }

        $firstSlash = substr($this->logFileName, 0, 1);
        if ($firstSlash === '/') {
            $this->logFileName = ltrim($this->logFileName, '/');
        }

        if ($this->root) {
            $this->fullLogFilePath = ZXC_ROOT . DIRECTORY_SEPARATOR . $this->logsFolder . DIRECTORY_SEPARATOR . $this->logFileName;
        } else {
            $this->fullLogFilePath = $this->logsFolder . DIRECTORY_SEPARATOR . $this->logFileName;
        }
        $this->fullLogFilePath = Helper::fixDirectorySlashes($this->fullLogFilePath);
    }

    /**
     * @method getDate
     * @return string
     * @throws Exception
     */
    public function getDate()
    {
        return (new DateTime())->format($this->dateFormat);
    }

    public function contextStringify(array $context = [])
    {
        return !empty($context) ? json_encode($context) : null;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @return null
     */
    public function getLogsFolder()
    {
        return $this->logsFolder;
    }

    /**
     * @param null $logsFolder
     */
    public function setLogsFolder($logsFolder)
    {
        $this->logsFolder = $logsFolder;
    }

    /**
     * @return null
     */
    public function getFullLogFilePath()
    {
        $this->updateFullLogFilePath();
        return $this->fullLogFilePath;
    }

    /**
     * @return null
     */
    public function getLogFileName()
    {
        return $this->logFileName;
    }

    /**
     * @param null $logFileName
     */
    public function setLogFileName($logFileName)
    {
        $this->logFileName = $logFileName;
    }

    /**
     * @param $logFileName
     * @method withLogFileName
     * @return Logger
     */
    public function withLogFileName($logFileName)
    {
        $new = clone $this;
        $new->logFileName = $logFileName;
        return $new;
    }
}