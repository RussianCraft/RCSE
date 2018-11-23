<?php
declare(strict_types=1);
namespace Core;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}
if (defined("RECONFIG_REQUIRED") === false) {
    define("RECONFIG_REQUIRED", "Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n");
}
if (defined("REPORT_ERROR") === false) {
    define("REPORT_ERROR", "Check your source code or send this message (with error) to Issues at GitHub!\n");
}

/**
 * class DBManager
 * Database Manager, provides access to database
 */
class DBManager
{
    private $logger;
    private $error_handler;
    private $config;
    private $debug;

    public function __construct()
    {
        $this->config = new JSONManager();
        $this->logger = new LogManager(get_class($this), $this->config);
        $this->error_handler = new Handlers\ErrorHandler();
        $this->debug = $this->config->get_main_config()['debug'];
    }

    

}