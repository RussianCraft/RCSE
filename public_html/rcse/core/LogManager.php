<?php
declare(strict_types=1);
namespace RCSE\Core;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}
if (defined("RECONFIG_REQUIRED") === false) {
    define("RECONFIG_REQUIRED", "Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n");
}
if (defined("REPORT_ERROR") === false) {
    define("REPORT_ERROR", "Check your source code or send this message (with error) to Issues at GitHub!\n");
}
define("ERROR_PREFIX_LOG", "LogManager Error: ");
define("ERROR_INIT_LOG", "Failed to initialize logging!\n");
define("ERROR_WRITE", "Failed to write to log file!\n");

/**
 * class Logger
 * Provides logging features
 */
class LogManager
{
    private $configurator;
    private $error_handler;
    private $log_file;
    private $log_handler;
    private $debug;

    /**
     * Initiates logging to $file, if enabled in config.
     *
     * @param string $file Filename
     * @param Core\JSONManager $configurator
     */
    public function __construct(string $file, JSONManager $configurator)
    {
        $this->config = $configurator;
        $this->error_handler = new Handlers\ErrorHandler();
        
        if ($this->config->get_data_json('main',['entry' => 'site'],false)['log'] === true) {
            try {
                $this->init_log($file);
                $this->debug = $this->config->get_data_json('main',['entry' => 'site'],false)['debug'];
            } catch (\Exception $e) {
                $message = ERROR_PREFIX_LOG . "(" . $e->getCode() . ") " . $e->getMessage() . REPORT_ERROR;
                $this->error_handler->print_error_no_log($message);
            }
        }
    }

    public function __destruct()
    {
        $this->write_to_log("Log end.\n", "info");

        if ($this->log_file != null && empty($this->log_file) === false) {
            fclose($this->log_handler);
        }
    }

    /**
     * Creates and initiates the log file, returns true is succseeds, false if fails
     *
     * @param string $file Filename
     * @return boolean
     */
    private function init_log(string $file) : bool
    {
        $datetime = date("Y-m-d_H-i-s");
        $dir = ROOT . "/logs/" . $datetime . "/";
        $file = str_replace('\\', '-', $file);
        $this->log_file =  $dir . $file . ".log";

        if (is_dir($dir) === false) {
            mkdir($dir, 0777);
        }

        $this->log_handler = fopen($this->log_file, "cb");
        
        if ($this->log_handler === false) {
            throw new Exceptions\FileCreationException($this->log_file);
        }
        
        if (fwrite($this->log_handler, "RCSE Log, Module: " . $file . ". Date-Time: ". $datetime . "\n\r") === false) {
            fclose($this->log_handler);
            throw new Exceptions\FileWriteException($this->log_file);
        }

        if ($this->debug === false) {
            fwrite($this->log_handler, "Debug logging is disabled!\n\r");
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param string $message
     * @return boolean
     */
    public function write_to_log(string $message, string $level) : bool
    {
        $datetime = date("Y/m/d H:i:s");
        $message_write = $datetime . " ";
        $level = strtolower($level);

        switch ($level) {
            case "debug":
                if ($this->debug) {
                    $message_write .= "[Debug]: ";
                } else {
                    exit;
                }
                break;
            case "info":
                $message_write .= "[Info]: ";
                break;
            case "notice":
                $message_write .= "[Notice]: ";
                break;
            case "warn":
            case "warning":
                $message_write .= "[Warning]: ";
                break;
            case "err":
            case "error":
                $message_write .= "[Error]: ";
                break;
            case "critical":
                $message_write .= "[Critical]: ";
                break;
            case "alert":
                $message_write .= "[Alert]: ";
                break;
            case "emergency":
                $message_write .= "[Emergency]: ";
                break;
            default:
                $message_write .= "[]: ";
        }
        
        if (fwrite($this->log_handler, $message_write . $message . "\r") === false) {
            $message = ERROR_PREFIX_LOG . ERROR_WRITE . REPORT_ERROR;
            $this->error_handler->print_error_no_log($message);
            return false;
        }

        return true;
    }
}
