<?php
declare(strict_types=1);
namespace RCSE\Core;

/**
 * class Logger
 * Provides logging features
 */
class Logger
{
    private 
        $configurator,
        $datetime, 
        $logfile,
        $loghadle;

    function __construct(string $file)
    {
        $this->configurator = new Configurator();
        
    }

    /**
     * Creates and initiates the log file, returns true is succseeds, false if failed
     *
     * @param string $file Filename
     * @return boolean
     */
    private function init_log(string $file) : bool
    {
        $this->datetime = date("Y-m-d_H-i-s");
        $this->logfile = "./logs/" + $this->datetime + "/" + $file + ".log";
        try {
            $this->loghadle = fopen($this->logfile, "cb");
        } 
        catch (Exception $e) {
            echo ("Logger error (" + $e->getCode() + "): " + $e->getMessage() );
            return false;
        }
        fwrite($this->loghadle, "RCSE Log, Module: " + $file + ". Date-Time: " + $this->datetime + "\n");

        return true;
    }

}