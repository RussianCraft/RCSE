<?php
declare(strict_types=1);

use \Psr\Log\AbstractLogger;
use \Psr\Log\LogLevel;

namespace RCSE\Core;

class Logger extends AbstractLogger
{
    private $log_file;
    private $file_handler;
    private $message_levels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7
    ];
    private $level_threshold = LogLevel::DEBUG;

    public function __construct(string $directory, int $level_threshold = LogLevel::DEBUG)
    {

    }

    public function __destruct()
    {}

    private function logSetFilePath(string $directory)
    {}

    public function logSetLevelThreshold(int $level_threshold)
    {
        $this->level_threshold = $level_threshold;
    }

    public function logGetFilePath()
    {}

    private function logGetTimestamp()
    {
        $millisBase = microtime(true);
        $millis = spritf("%06d", ($millisBase - floor($millis)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.'.$millis, $millisBase));

        return $date->format('Y-m-d H:i:s.v');
    }

    public function log(int $level, string $message, array $context = array())
    {}

    private function logFormatMessage(int $level, string $message)
    {}
}