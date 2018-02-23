<?php

namespace ZXC\Native;

use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{
    private $dateFormat = DateTime::RFC2822;
    private $filePath;
    private $template = "{date} {level} {message} {context}";
    private $level;

    public function __construct(array $config = [])
    {
        $this->initialize($config);
    }

    public function initialize(array $config = [])
    {
        $this->level = isset($config['applevel']) ? $config['applevel'] : 'production';
        if (isset($config['settings']['filePath'])) {
            if (isset($config['settings']['root']) && $config['settings']['root'] === true) {
                $this->filePath = ZXC_ROOT . DIRECTORY_SEPARATOR . $config['settings']['filePath'];
            } else {
                $this->filePath = $config['settings']['filePath'];
            }
            if (!file_exists($this->filePath)) {
                touch($this->filePath);
            }
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        file_put_contents($this->filePath, trim(strtr($this->template, [
                '{date}' => $this->getDate(),
                '{level}' => $level,
                '{message}' => $message,
                '{context}' => $this->contextStringify($context),
            ])) . PHP_EOL, FILE_APPEND);
    }

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
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }
}