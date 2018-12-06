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

    /**
     * Surprisingly, initializes the DB Manager: obtains DB creditans from config, creates MySQL connection via PDO, if succeeds returns true, else - false + error
     *
     * @return boolean
     */
    private function init_db() : bool
    {
        $props = $this->config->get_main_config("database");
        $dsn = 'mysql:host=' . $props['host'] . ';port=' . $props['port'] . ';dbname=' . $props['name'];

        $this->logger->write_to_log("Initializing the DB connection.\n", "info");

        try {
            $this->database = new \PDO($dsn, $props['login'], $props['passw']);
        } catch (\PDOException $e) {
            $message = ERROR_PREFIX_DB . "(" .$e->getCode() . ")". $e->getMessage() . "!\n" . ERROR_INIT_DB . REPORT_ERROR;
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        $this->logger->write_to_log("DB connection initialized successfully!", "info");

        return true;
    }

    public function get_data_db(string $table, string $type, string $marker="")
    {
        $table = strtolower($table);
        $type = strtolower($type);

        $this->logger->write_to_log("Obtaining data for $marker from DB.", "info");

        if ($this->check_data_db($table, $type, $marker) === false) {
            $this->logger->write_to_log("Data for $marker was not found!", "warning");
            return false;
        }

        $this->logger->write_to_log("Setting up the query.", "info");

        $query = $this->config->get_queries($table)["select_" . $type];

        if ($query === false) {
            $message = ERROR_PREFIX_DB . ERROR_QUERY_NF . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        $temp = explode(":", $query);
        $params = [':' . $temp[1] => $marker];
        unset($temp);
        
        $this->logger->write_to_log("Preparing the query.", "info");

        try {
            $query = $this->database->prepare($query);
        } catch (PDOException $e) {
            $message = ERROR_PREFIX_DB . "(" .$e->getCode() . ")". $e->getMessage() . "!\n" . ERROR_PREPARE . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }
        
        $this->logger->write_to_log("Executing the query", "info");

        $query_bool = $query->execute($params);

        if ($query_bool === false) {
            $this->logger->write_to_log("Query execution failed!", "error");
            return false;
        }

        $this->logger->write_to_log("Successfully obtained the data!", "info");

        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    public function send_data_db(string $table, string $type, array $contents, string $marker="") : bool
    {
        $table = strtolower($table);
        $type = strtolower($type);

        $this->logger->write_to_log("Sending data for $marker to DB.", "info");
        $this->logger->write_to_log("Setting up the query.", "info");

        $query = $this->config->get_queries($table)[$type];

        if ($query === false) {
            $message = ERROR_PREFIX_DB . ERROR_QUERY_NF . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }
        
        $this->logger->write_to_log("Cleaning the query parameters.", "debug");

        $temp = explode(":", $query);
        array_shift($temp);

        if ($type === "insert") {
            for ($i = 0; $i < count($temp); $i++) {
                $temp[$i] = str_replace(array(',', ')'), "", $temp[$i]);
            }
        } else {
            for ($i = 0; $i < count($temp); $i++) {
                $temp[$i] = explode(' ', $temp[$i]);
            }
            for ($i = 0; $i < count($temp); $i++) {
                $temp[$i] = $temp[$i][0];
            }
            for ($i = 0; $i < count($temp); $i++) {
                $temp[$i] = str_replace(',', "", $temp[$i]);
            }
        }

        if ($type === "insert") {
            for ($i  = 0; $i < count($temp); $i++) {
                $params[':' . $temp[$i]] = $contents[$temp[$i]];
            }
        } else {
            for ($i  = 0; $i < count($temp); $i++) {
                $params[':' . $temp[$i]] = $contents[$temp[$i]];
            }

            $params[':' . end($temp)] = $marker;
        }

        unset($temp);

        $this->logger->write_to_log("Preparing the query.", "info");

        try {
            $query = $this->database->prepare($query);
        } catch (PDOException $e) {
            $message = ERROR_PREFIX_DB . "(" .$e->getCode() . ")". $e->getMessage() . "!\n" . ERROR_PREPARE . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        $this->logger->write_to_log("Executing the query", "info");

        $query_bool = $query->execute($params);

        if ($query_bool === false) {
            $this->logger->write_to_log("Query execution failed!", "error");
            return false;
        }

        $this->logger->write_to_log("Successfully obtained the data!", "info");

        return $query_bool;
    }

    public function check_data_db(string $table, string $type, string $marker="") : bool
    {
        $table = strtolower($table);
        $type = strtolower($type);

        $this->logger->write_to_log("Checking data for $marker to DB.", "info");
        $this->logger->write_to_log("Setting up the query.", "info");
 
        $query = $this->config->get_queries($table)["select_" . $type];

        if ($query === false) {
            $message = ERROR_PREFIX_DB . ERROR_QUERY_NF . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        $temp = explode(":", $query);
        $params = [':' . $temp[1] => $marker];
        unset($temp);

        $this->logger->write_to_log("Preparing the query.", "info");

        try {
            $query = $this->database->prepare($query);
        } catch (PDOException $e) {
            $message = ERROR_PREFIX_DB . "(" .$e->getCode() . ")". $e->getMessage() . "!\n" . ERROR_PREPARE . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        $this->logger->write_to_log("Executing the query", "info");
        
        $query_bool = $query->execute($params);

        if ($query_bool === false) {
            $this->logger->write_to_log("Query execution failed!", "error");
            return false;
        }

        $this->logger->write_to_log("Successfully obtained the data!", "info");

        return $query_bool;
    }

    public function delete_data_db(string $table, string $marker="") : bool
    {
        $table = strtolower($table);

        $this->logger->write_to_log("Checking data for $marker to DB.", "info");
        $this->logger->write_to_log("Setting up the query.", "info");

        $query = $this->config->get_queries($table)["delete"];

        if ($query === false) {
            $message = ERROR_PREFIX_DB . ERROR_QUERY_NF . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        $temp = explode(":", $query);
        $params = [':' . $temp[1] => $marker];
        unset($temp);

        try {
            $query = $this->database->prepare($query);
        } catch (PDOException $e) {
            $message = ERROR_PREFIX_DB . "(" .$e->getCode() . ")". $e->getMessage() . "!\n" . ERROR_PREPARE . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }
        
        $this->logger->write_to_log("Preparing the query.", "info");

        $query_bool = $query->execute($params);

        if ($query_bool === false) {
            $this->logger->write_to_log("Query execution failed!", "error");
            return false;
        }

        $this->logger->write_to_log("Successfully obtained the data!", "info");

        return $query_bool;
    }
}
