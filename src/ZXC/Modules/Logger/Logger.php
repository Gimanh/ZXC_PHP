<?php


namespace ZXC\Modules\Logger;


use DateTime;
use ZXC\Native\Helper;
use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;
use ZXC\Interfaces\Psr\Log\LogLevel;
use ZXC\Interfaces\Psr\Log\AbstractLogger;
use ZXC\Interfaces\Psr\Log\LoggerInterface;


class Logger extends AbstractLogger implements LoggerInterface, IModule
{
    use Module;

    protected $dateFormat = DateTime::RFC2822;

    protected $logFileName = '';

    protected $folder = '';

    protected $template = "";

    /** @var string */
    protected $level = LogLevel::CRITICAL;

    protected $fullPath = null;

    private $lvlToValue = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7,
    ];

    public function init(array $config = [])
    {
        $this->level = $config['lvl'] ?? LogLevel::CRITICAL;
        $this->logFileName = $config['fileName'] ?? 'ZXC_Application.log';
        $this->folder = $config['folder'] ?? sys_get_temp_dir();
        $this->fullPath = $this->folder . '/' . $this->logFileName;
        $this->template = $config['template'] ?? "{date} | {level} | {ip} | {message} | {context}";

        if (!file_exists($this->fullPath)) {
            touch($this->fullPath);
        }
    }

    public function log($level, $message, array $context = [])
    {
        if ($this->lvlToValue[$level] <= $this->lvlToValue[$this->level]) {
            file_put_contents($this->fullPath, trim(strtr($this->template, [
                    '{date}' => $this->getDate(),
                    '{level}' => $level,
                    '{ip}' => Helper::getIp(),
                    '{message}' => $message,
                    '{context}' => $this->contextStringify($context),
                ])) . PHP_EOL, FILE_APPEND);
        }
    }

    public function getDate()
    {
        return (new DateTime())->format($this->dateFormat);
    }

    public function contextStringify(array $context = [])
    {
        return !empty($context) ? json_encode($context) : null;
    }
}
