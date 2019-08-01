<?php

namespace ZXC\Traits;

use ZXC\ZXC;
use Exception;

trait Reportable
{
    protected $writeLog = true;

    protected $logFileName = '';
    /**
     * @var string[]
     */
    protected $reportMessages = [];

    /**
     * @return string
     */
    public function getReportMessage()
    {
        return implode(' | ', $this->reportMessages);
    }

    /**
     * @param string $message
     * @method addReportMessage
     * @throws Exception
     */
    public function addReportMessage($message)
    {
        $this->reportMessages[] = $message;
        if ($this->writeLog) {
            ZXC::log($message, [], $this->logFileName);
        }
    }
}