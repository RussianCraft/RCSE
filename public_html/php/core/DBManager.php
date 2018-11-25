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
define("ERROR_PREFIX_DB", "DBManager Error: ");
define("ERROR_INIT_DB", "Failed to initialize DBManager!\n");
define("ERROR_QUERY_NF", "Requested query does not exist!\n");
define("ERROR_PREPARE", "Failed to prepare query!\n");

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
    private $database;

    public function __construct()
    {
        $this->config = new JSONManager();
        $this->logger = new LogManager(get_class($this), $this->config);
        $this->error_handler = new Handlers\ErrorHandler();
        $this->debug = $this->config->get_main_config()['debug'];
        $this->init_db();
    }

    public function init_db() : bool
    {
        $props = $this->config->get_main_config("database");
        $dsn = 'mysql:host=' . $props['host'] . ';port=' . $props['port'] . ';dbname=' . $props['name'];

        try {
            $this->database = new \PDO($dsn, $props['login'], $props['passw']);
        } catch (\PDOException $e) {
            $message = ERROR_PREFIX_DB . "(" .$e->getCode() . ")". $e->getMessage() . "!\n" . ERROR_INIT_DB . REPORT_ERROR;
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        return true;
    }

    public function get_data(string $table, string $type, string $marker="") : array
    {
        
    }

    public function send_data() : bool {}

    public function check_data(string $table, string $type, string $marker="") : bool 
    {
        $table = strtolower($table);
        $type = strtolower($type);

        $query = $this->config->get_query($table)[$type];

        if($query === false) {
            $message = ERROR_PREFIX_DB . ERROR_QUERY_NF . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        $temp = explode(":", $query);
        $params = [':' . $temp[1] => $marker];
        unset($temp);

        try {
            $query = $this->database->prepare($query);
        } catch(PDOException $e) {
            $message = ERROR_PREFIX_DB . "(" .$e->getCode() . ")". $e->getMessage() . "!\n" . ERROR_PREPARE . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }


    }
}
