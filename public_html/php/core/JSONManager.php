<?php
declare(strict_types=1);
namespace Core;
error_reporting(-1);

define("ROOT", $_SERVER['DOCUMENT_ROOT']);
define ("ERROR_PREFIX", "JSONManager Error: ");
define("ERROR_INCORRECT_CONFIG_TYPE", "Incorrect config type or not selected, check source code of your installation or send this message (with error) to Issues at GitHub!");
define("ERROR_RECONFIG_REQUIRED", "Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n");
define("ERROR_LOCALE_NOT_FOUND", "Selected locale not found or incorrect, check source code of your installation or send this message (with error) to Issues at GitHub!");

/**
 * class JSONManager
 * Parses from and to JSON config files
 */
class JSONManager
{
    private 
        $logger,
        $error_handler,
        $msg_errors,
        $msg_info;
 
    function __construct()
    {
        $this->error_handler = new Handlers\error_handler();
        $this->logger = new LogManager(get_class($this), $this);
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
        $file_path = ROOT . $file;

        if (file_exists($file_path)) {
            $file_handler = fopen($file_path, "rb");

            $file_contents = fread($file_handler, filesize($file_path));
            
            fclose($file_handler);

            return $file_contents;
        } else {
            throw new Exceptions\FileNotFoundException($file_path);
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
        $file = $this->read_file("/configs/main.json");
        } catch (Exceptions\FileNotFoundException $e) {
            $message = ERROR_PREFIX . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n" . ERROR_RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
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
                $message = ERROR_PREFIX . ERROR_INCORRECT_CONFIG_TYPE;
                $this->error_handler->config_error($message);
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
        $file = $this->read_file("/configs/queries.json");
        } catch (Exceptions\FileNotFoundException $e) {
            $message =  ERROR_PREFIX . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n" . ERROR_RECONFIG_REQUIRED;
            $this->error_handler->config_error($message);
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

    public function get_locale(string $section, string $element, string $lang, string $type) : array
    {

        switch($section) {
            case 'CMS':
                switch($element) {
                    case 'common':
                        $file = '/locale/CMS/common.json';
                        break;
                    default:
                        $message = ERROR_PREFIX . ERROR_LOCALE_NOT_FOUND;
                        $this->error_handler->print_error_and_redirect($message, "admin");
                }
                break;
        }

        try {
            $file = $this->read_file($file);
            } catch (Exceptions\FileNotFoundException $e) {
                $message =  ERROR_PREFIX . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n" . ERROR_RECONFIG_REQUIRED;
                $this->error_handler->config_error($message);
                die;
            }
    
            $json = json_decode($file, true);
    }

}
