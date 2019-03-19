<?php
declare(strict_types=1);
namespace RCSE\Core;

class Logger
{
    private $log_file;
    private $file_handler;
    private $message_levels = [
        "Emergency",
        "Alert",
        "Critical",
        "Error",
        "Warning",
        "Notice",
        "Info",
        "Debug"
    ];
    private $level_threshold;

    public function __construct(string $directory, int $level_threshold)
    {

    }

    public function __destruct()
    {}

    private function logSetFilePath(string $directory)
    {}

    public function logSetLevelThreshold(int $level_threshold)
    {}

    public function logGetFilePath()
    {}

    private function logGetTimestamp()
    {}

    public function logWrite(int $level, string $message)
    {}

    private function logFormatMessage(int $level, string $message)
    {}
}