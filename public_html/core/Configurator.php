<?php
declare(strict_types=1);
namespace Core;
error_reporting(-1);

define("ROOT", $_SERVER['DOCUMENT_ROOT']);
define ("ERROR_PREFIX", "Configurator Error: ");

/**
 * class Configurator
 * Parses from and to JSON config files
 */
class Configurator
{
    private 
        $logger,
        $errorHandler;
 
    function __construct()
    {
        $this->errorHandler = new ErrorHandler();
        $this->logger = new Logger(get_class($this), $this);
    }

    /**
     * Reads contents of /configs/$file, if it exists, returns contents_of_file, else throws FileNotFoundException
     *
     * @param string $file filename, must end with .json (i.e. "main.json)
     * @throws Exceptions\FileNotFoundException
     * @return string
     */
    private function read_file(string $file) : string
    {
        $file_handler; $file_contents;
        $file_path = ROOT. "/configs/" . $file;

        if (file_exists($file_path)) {
            $file_handler = fopen($file_path, "rb");

            $file_contents = fread($file_handler, filesize($file_path));
            
            fclose($file_handler);

            return $file_contents;
        } else {
            throw new Exceptions\FileNotFoundException($file_path);
            //no return
        }

    }

    /**
     * Reads main.json and parses it, if failed echoes "File not found" and redirects to admin.php to reconfigure, 
     * if succeeds returns array with chosen properties
     * 
     * @param string $type config type, defalut "site"
     * @return array
     */
    public function get_main_config(string $type="site") : array
    {
        try {
        $file = $this->read_file("main.json");
        } catch (Exceptions\FileNotFoundException $e) {
            $message = ERROR_PREFIX . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n";
            $this->errorHandler->print_error_and_redirect($this->logger, $message, "admin");
            die;
        }

        $json = json_decode($file, true);
        var_dump($json);

        switch(strtolower($type)) {
            case "site":
                return $json['site'];
            case "db":
            case "database":
                return $json['database'];
            default:
                $message = ERROR_PREFIX . "Incorrect config type or not selected, check source code of your installation or send this message (with error) to Issues at GitHub!";
                $this->errorHandler->config_error($message);
                die;
        }
    }
    
    /**
     * Reads queries.json and parses it, if failed echoes "File not found" and redirects to admin.php to reconfigure, 
     * if succeeds returns array with chosen queries
     * 
     * @param string $type query type
     * @return array
     */
    public function get_queries(string $type) : array
    {
        try {
        $file = $this->read_file("queries.json");
        } catch (Exceptions\FileNotFoundException $e) {
            $message = "Configurator Error (" . $e->getCode() . "): " . $e->getMessage() . "!\n Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n";
            $this->errorHandler->config_error($message);
            die;
        }

        $json = json_decode($file, true);
        var_dump($json);

        switch(strtolower($type)) {
            case "users":
            case "usr":
                return $json['users'];
            case "bans":
                return $json['bans'];
            case "news":
                return $json['news'];
            case "comments":
            case "holywar":
                return $json['comments'];
            default:
                echo "Error!";
                exit;
        }
    }

}