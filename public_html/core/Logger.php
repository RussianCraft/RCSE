<?php
declare(strict_types=1);
namespace Core;
error_reporting(-1);

/**
 * class Logger
 * Provides logging features
 */
class Logger
{
    private 
        $configurator,
        $errorHandler,
        $datetime, 
        $log_file,
        $log_handler;

    /**
     * Initiates logging to $file, if enabled in config. Returns array with [true, "ready"] if enabled, else returns [false, "disabled"]
     *
     * @param string $file Filename
     * @return array
     */
    public function __construct(string $file, Configurator $configurator) 
    {
        $this->configurator = $configurator;

        if ($this->configurator->is_logging_enabled()) {
            $this->init_log($file);
            return [true, "Ready"];
        } else {
            return [false, "Disabled"];
        }
    }

    public function __destruct() 
    {
        fclose($log_handler);
    }

    /**
     * Creates and initiates the log file, returns true is succseeds, false if fails
     *
     * @param string $file Filename
     * @return boolean
     */
    private function init_log(string $file) : bool
    {
        $this->datetime = date("Y-m-d_H-i-s");
        $this->log_file = "./logs/" + $this->datetime + "/" + $file + ".log";

        try {
            $this->log_handler = fopen($this->logfile, "cb");
        } catch (Exception $e) {
            echo ("Logger error (" + $e->getCode() + "): " + $e->getMessage() );
            return false;
        }

        fwrite($this->log_handler, "RCSE Log, Module: " + $file + ". Date-Time: " + $this->datetime + "\n");

        if(!$this->is_debug_enabled()) {
            fwrite($this->log_handler, "Debug logging is disabled!");
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param string $message
     * @return boolean
     */
    public function write_to_log(string $message, string $error_level) : bool
    {
        $datetime = date("Y/m/d H:i:s");
        $message = $datetime . " ";

        switch($error_level) {
            case "debug":
                if($this->is_debug_enabled()) {
                    $message .= "[Debug]: ";
                } else {
                    exit;
                }
                break;
            case "info":
                $message .= "[Info]: ";
                break;
            case "notice":
                $message .= "[Notice]";
                break;
            case "warn":
            case "warning":
                //placeholder
                break;
            case "err":
            case "error":
                //placeholder
                break;
            case "critical":
                //placeholder
                break;
            case "alert":
                //placeholder
                break;
            case "emergency":
                //placeholder
                break;
            default:
                //placeholder
        }
        
        try {
            fwrite($this->log_handler, $datetime . "[]: " . $message);
        } catch(Exception $e) {

        }
    }

    private function is_debug_enabled() : bool 
    {
        $properties = $this->configurator->get_module_properties("logger");

        return $properties['debug'];
    }

}