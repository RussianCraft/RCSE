<?php
declare(strict_types=1);

namespace RCSE\Core;

class Logger
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    private $log_file;
    private $log_dir;
    private $file_handler;
    private $utils;
    private $file_perms = 0777;
    private $message_levels = [
        self::EMERGENCY => 0,
        self::ALERT => 1,
        self::CRITICAL => 2,
        self::ERROR => 3,
        self::WARNING => 4,
        self::NOTICE => 5,
        self::INFO => 6,
        self::DEBUG => 7
    ];
    private $level_threshold = self::DEBUG;

    public function __construct($level_threshold = self::DEBUG)
    {
        $this->utils = new Utils();

        $this->logSetLevelThreshold($level_threshold);
        $this->logSetFilePath();

        $this->file_handler = new File($this->log_dir, $this->log_file);
        $this->file_handler->fileOpen("c");

    }

    public function __destruct()
    {
        $this->file_handler->__destruct();
    }

    private function logSetFilePath()
    {
        $datetime = $this->logGetTimestamp(false)->format('Y-m-d_H-i-s');
        $path = "/logs/{$this->utils->utilsGetClientIP()}/";

        if(is_dir(ROOT . $path) === false) {
            mkdir(ROOT . $path, $this->file_perms);
        }

        $file = "{$datetime}.log";

        $this->log_dir = $path;
        $this->log_file = $file;
    }

    public function logSetLevelThreshold($level_threshold)
    {
        $this->level_threshold = $level_threshold;
    }

    public function logGetFilePath()
    {
        return $this->log_dir;
    }

    private function logGetTimestamp(bool $formatted = true)
    {
        $stamp = date('Y-m-d H:i:s.v');
        $date = new \DateTime($stamp);
        
        if($formatted) {
            return $date->format('Y-m-d H:i:s.v');
        } else {
            return $date;
        }
    }

    public function log($level, string $message, string $source)
    {
        if($this->message_levels[$this->level_threshold] <= $this->message_levels[$level]) {
            return;
        }

        $message_formatted = $this->logFormatMessage($level, $message, $source);

        $this->file_handler->fileWriteLine($message_formatted);
    }

    private function logFormatMessage(string $level, string $message, string $source)
    {
        $level = strtoupper($level);
        $message_formatted = "[{$this->logGetTimestamp()}][{$level}][{$source}] {$message}";
        return $message_formatted.PHP_EOL;
    }
}