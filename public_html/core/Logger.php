<?php
declare(strict_types=1);
namespace Core;

/**
 * class Logger
 * Provides logging features
 */
class Logger
{
    private 
        $configurator,
        $datetime, 
        $log_file,
        $log_handler;

    /**
     * Initiates logging to $file, if enabled in config. Returns array with [true, "ready"] if enabled, else returns [false, "disabled"]
     *
     * @param string $file Filename
     * @return array
     */
    public function __construct(string $file) 
    {
        $this->configurator = new Configurator();

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

        return true;
    }

    /**
     * Undocumented function
     *
     * @param string $message
     * @return boolean
     */
    public function write_to_log(string $message) : bool
    {
        $datetime = date("Y/m/d H:i:s");
        
        try {
            fwrite($this->log_handler, "[" . $datetime . "]: " . $message);
        } catch(Exception $e) {

        }
    }

}