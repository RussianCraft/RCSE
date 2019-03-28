<?php
declare(strict_types=1);
namespace RCSE\Core;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

/**
 * Database Manager, provides access to database operations
 */
class Database
{
    /** @var Logger */
    private $logger;

    /** @var Configurator */
    private $config;

    /** @var \PDO */
    private $database;

    public function __construct(Logger $logger, Configurator $configurator)
    {
        $this->config = $configurator;
        $this->logger = $logger;
        $this->databaseInit();
    }

    /**
     * Surprisingly, initializes database connection, via PDO
     *
     * @return void Doesn't return anything, but fills $database variable of class with exemplar of PDO
     * @throws \Exception In case of PDO connection to database failure
     */
    private function databaseInit()
    {
        $props = $this->config->configObtainMain('database');
        $dsn = 'mysql:host=' . $props['host'] . ';port=' . $props['port'] . ';dbname=' . $props['name'];

        $this->logger->log($this->logger::INFO, "Initializing Database connection (host: {$props['host']}:{$props['port']}, name: {$props['name']}).", get_class($this));

        try {
            $this->database = new \PDO($dsn, $props['login'], $props['passw'], [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"]);
        } catch (\PDOException $e) {
            $this->logger->log($this->logger::CRITICAL, "Failed to connect to database - {$e->getCode()}: {$e->getMessage()}.", get_class($this));
            throw new \Exception($e->getMessage(), (int)$e->getCode());
        }

        $this->logger->log($this->logger::INFO, "Database connected successfully.", get_class($this));
    }

    /**
     * Obtains data from DB
     *
     * @param string $table Table containing data
     * @param string $type  Type of marker (e.g. "by_login", "all", "by_type", etc.), described in queries.json file
     * @param string $marker Data to be used as marker (user login, post author, or null for type "all" query)
     * @return array Associative array of data
     * @throws \Exception In case of prepare or execution failure
     */
    public function databaseGetData(string $table, string $type, string $marker="") : array
    {
        $table = strtolower($table);
        $type = strtolower($type);

        $this->logger->log($this->logger::INFO, "Trying to obtain data from {$table}.", get_class($this));

        $query_statement = $this->config->configObtainQueries($table)["select_" . $type];

        if ($query_statement['params'][':marker'] !== null) {
            $params[":marker"] = $marker;
        }

        try {
            $query = $this->databasePrepareAndExecute($query_statement['query'], $params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $this->logger->log($this->logger::INFO, "Data obtained successfully.", get_class($this));

        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Sends $contents to $table
     *
     * @param string $table Table to send data to
     * @param string $type Type of query (e.g. "insert", "update", etc), described in queries.json
     * @param array $contents Array of data to send
     * @param string $marker Data to be used as marker (user login, comment id, or null in case of "insert" query)
     * @return boolean True if succeeds, false if fails (duh)
     * @throws \Exception In case of prepare or execution failure
     */
    public function databaseSendData(string $table, string $type, array $contents, string $marker="") : bool
    {
        $table = strtolower($table);
        $type = strtolower($type);

        $this->logger->log($this->logger::INFO, "Trying to send data ({$type}) from {$table}.", get_class($this));

        if (strpos($type, 'select') > 0) {
            $this->logger->log($this->logger::ERROR, "For 'select_*' queries use 'databaseGetData'.", get_class($this));
            return false;
        } elseif (strpos($type, 'delete') > 0) {
            $this->logger->log($this->logger::ERROR, "For 'delete' queries use 'databaseDeleteData'.", get_class($this));
            return false;
        }

        $query_statement = $this->config->configObtainQueries($table)[$type];

        if ($type === "insert") {
            foreach ($query_statement['params'] as $key => $value) {
                    $params[$key] = $contents[$key];
            }
        } else {
            foreach ($query_statement['params'] as $key => $value) {
                if ($key !== ":marker") {
                    $params[$key] = $contents[$key];
                }

            }
            $params[':marker'] = $marker;
        }
        unset($key);
        unset($value);

        try {
            $query = $this->databasePrepareAndExecute($query_statement['query'], $params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        if ($query->rowCount() <= 0) {
            $this->logger->log($this->logger::ERROR, "Failed to send data.", get_class($this));
            return false;
        }

        $this->logger->log($this->logger::INFO, "Data sent successfully.", get_class($this));
        return true;
    }

    /**
     * Checks, whether data for $marker present in $table
     *
     * @param string $table  Table containing data
     * @param string $type   Type of marker (e.g. "by_login", "by_type", etc.), described in queries.json file
     * @param string $marker Data to be used as marker (user login, post author, or null for type "all" query)
     * @return boolean True, if data present, and false if not
     * @throws \Exception In case of prepare or execution failure
     */
    public function databaseCheckData(string $table, string $type, string $marker="") : bool
    {
        $table = strtolower($table);
        $type = strtolower($type);

        $this->logger->log($this->logger::INFO, "Checking {$table} for data existence.", get_class($this));

        $query_statement = $this->config->configObtainQueries($table)["check_" . $type];

        $params[":marker"] = $marker;

        try {
            $query = $this->databasePrepareAndExecute($query_statement['query'], $params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $this->logger->log($this->logger::INFO, "Successfully obtained the data!", get_class($this));

        return (bool)$query->fetchColumn();
    }

    /**
     * Deletes data for $marker from $table
     *
     * @param string $table Table containing data
     * @param string $marker Data to be used as marker (usually, unique ID of database entry)
     * @return boolean True if succeeds, false if fails
     * @throws \Exception In case of prepare or execution failure
     */
    public function databaseDeleteData(string $table, string $marker) : bool
    {
        $table = strtolower($table);

        $this->logger->log($this->logger::INFO, "Trying to delete (marker: {$marker}) data from {$table}.", get_class($this));

        $query_statement = $this->config->configObtainQueries($table)["delete"];

        $params[":marker"] = $marker;

        try {
            $query = $this->databasePrepareAndExecute($query_statement['query'], $params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        if ($query->rowCount() <= 0) {
            $this->logger->log($this->logger::ERROR, "Failed to delete data.", get_class($this));
            return false;
        }

        $this->logger->log($this->logger::INFO, "Data deleted successfully.", get_class($this));
        return true;
    }

    /**
     * Prepares provided $query and executes it with $params
     *
     * @param string $query Query to prepare and execute
     * @param array $params Query params
     * @return \PDOStatement Returns result of query execution
     * @throws \Exception In case of PDO query preparation failure
     * @throws \Exception In case of PDO query execution failure
     */
    private function databasePrepareAndExecute(string $query, array $params) : \PDOStatement
    {
        $this->logger->log($this->logger::INFO, "Trying to prepare query.", get_class($this));

        try {
            $query = $this->database->prepare($query);
        } catch (\PDOException $e) {
            $this->logger->log($this->logger::ERROR, "Failed to preapre query - ({$e->getCode()}): {$e->getMessage()}!", get_class($this));
            throw new \Exception($e->getMessage(), (int)$e->getCode());
        }
        
        $this->logger->log($this->logger::INFO, "Trying to execute query.", get_class($this));
        
        $query_bool = $query->execute($params);

        if ($query_bool === false) {
            $this->logger->log($this->logger::ERROR, "Failed to execute query!", get_class($this));
            throw new \Exception("Query execution failed!", 1020);
        }

        return $query;
    }
}
