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
        $this->logSetLevelThreshold($level_threshold);
        $this->logSetFilePath();

        $this->file_handler = new \RCSE\Core\Handlers\FileHandler();

    }

    public function __destruct()
    {}

    private function logSetFilePath()
    {
        $datetime = $this->logGetTimestamp(false)->format('Y-m-d_H:i:s');
        $path = "/logs/{$this->logGetClientIP()}/";

        if(is_dir($path) === false) {
            mkdir($path, 0777, true);
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
        $stamp = date('Y-m-d H:i:s');
        $date = new \DateTime($stamp);
        
        if($formatted) {
            return $date->format('Y-m-d H:i:s.v');
        } else {
            return $date;
        }
    }

    public function log($level, string $message, array $context = array())
    {
        if($this->message_levels[$this->level_threshold] < $this->message_levels[$level]) {
            return;
        }

        $message_formatted = $this->logFormatMessage($level, $message);

        $this->file_handler->fileWriteLine($this->log_dir, $this->log_file, $message_formatted);
    }

    private function logFormatMessage(string $level, string $message)
    {
        $message_formatted = "[{$this->logGetTimestamp()}][{$level}] {$message}";
        return $message_formatted.PHP_EOL;
    }

    /**
     * Получает IP адрес посетителя. КОСТЫЛЬ, ПЕРЕНЕСТИ В ДРУГОЙ КЛАСС!!!!
     *
     * @return string
     */
    private function logGetClientIP() : string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}