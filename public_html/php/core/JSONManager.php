<?php
declare(strict_types=1);
namespace Core;

error_reporting(-1);

define("ROOT", $_SERVER['DOCUMENT_ROOT']);
define("ERROR_PREFIX", "JSONManager Error: ");
define("ERROR_INCORRECT_CONFIG_TYPE", "Incorrect config type or not selected, check source code of your installation or send this message (with error) to Issues at GitHub!\n");
define("ERROR_RECONFIG_REQUIRED", "Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n");
define("ERROR_LOCALE_NOT_FOUND", "Selected locale not found or incorrect, check source code of your installation or send this message (with error) to Issues at GitHub!\n");
define("ERROR_CONFIG_STRUCTURE", "Tried to write config, but structure does not match!\n");
define("ERROR_CONFIG_UPDATE", "Tried to write new config data, but failed! Check your source code or send this message (with error) to Issues at GitHub!\n");
define("ERROR_JSON_DECODING", "Tried to decode parameters from JSON fomat, but failed!\n");
define("ERROR_JSON_ENCODING", "Tried to encode parameters to JSON fomat, but failed!\n");

/**
 * class JSONManager
 * Parses from and to JSON config files
 */
class JSONManager
{
    private $logger;
    private $error_handler;
    private $msg_errors;
    private $msg_info;
 
    public function __construct()
    {
        $this->error_handler = new Handlers\error_handler();
        $this->logger = new LogManager(get_class($this), $this);
    }

    /**
     * Reads contents of /$file(i.e. /configs/main.json), if file not present or not readable throws FileNotFoundException,
     * if present, but locked throws FileLockException
     *
     * @param string $file filename, must end with .json (i.e. "main.json)
     * @throws Exceptions\FileNotFoundException
     * @throws Exception\FileLockException
     * @return string
     */
    private function read_file(string $file) : string
    {
        $file_handler;
        $file_contents;
        $file_path = ROOT . $file;

        if (!is_readable($file_path)) {
            throw new Exceptions\FileNotFoundException($file_path);
        }

        $file_handler = fopen($file_path, "rb");

        if (!flock($file_handler, LOCK_SH, $eWouldBlock) || $eWouldBlock) {
            fclose($file_handler);
            throw new Exceptions\FileLockException($file_path);
        }
        
        $file_contents = fread($file_handler, filesize($file_path));
    
        flock($file_handler, LOCK_UN);
        fclose($file_handler);

        return $file_contents;
    }

    /**
     * Writes $contents to /$file (i.e. /configs/main.json), if file not present or not writeable throws FileNotFoundException,
     *  if writing failed throws FileWriteException, if succeeds returns true
     *
     * @param string $file
     * @param string $contents
     * @throws Exceptions\FileNotFoundException
     * @throws Exceptions\FileWriteException
     * @return boolean
     */
    private function write_file(string $file, string $contents) : bool
    {
        $file_handler;
        $file_path = ROOT . $file;

        if (!is_writeable($file_path)) {
            throw new Exceptions\FileNotFoundException($file_path);
        }

        $file_handler = fopen($file_path, "wb");

        while (!flock($file_handler, LOCK_EX, $eWouldBlock)) {
        }

        if (!fwrite($file_handler, $contents)) {
            fclose($file_handler);
            throw new Exceptions\FileWriteException($file_path);
        }

        return true;
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
        } catch (\Exception $e) {
            $message = ERROR_PREFIX . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n" . ERROR_RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            die;
        }

        $json = json_decode($file, true);

        if (!json) {
            $message = ERROR_PREFIX . ERROR_JSON_DECODING . ERROR_RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            die;
        }

        switch (strtolower($type)) {
            case "site":
                return $json['site'];
            case "db":
            case "database":
                return $json['database'];
            default:
                $message = ERROR_PREFIX . ERROR_INCORRECT_CONFIG_TYPE;
                $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
                die;
        }
    }

    public function set_main_config(string $type, array $contents) : bool
    {
        try {
            $file = $this->read_file("/configs/main.json");
        } catch (\Exception $e) {
            $message = ERROR_PREFIX . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . ERROR_RECOFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            die;
        }

        $json_orig = json_decode($file, true);
        
        if (!json_orig) {
            $message = ERROR_PREFIX . ERROR_JSON_DECODING;
            $this->error_handler->print_error($this->logger, $message);
            die;
        }
        
        
        foreach ($json_orig[$type] as $key => $value) {
            foreach ($contents as $key1 => $value1) {
                if ($key != $key1) {
                    $message = ERROR_PREFIX . ERROR_CONFIG_STRUCTURE;
                    $this->error_handler->print_error($this->logger, $message);
                    die;
                }
                 
                $json_orig[$type][$key] = $value1;
            }
        }

        $json = json_encode($json_orig);

        if (!$json) {
            $message = ERROR_PREFIX . ERROR_JSON_ENCODING;
            $this->error_handler->print_error($this->logger, $message);
            die;
        }

        try {
            $this->write_file($file, $json);
        } catch (\Exception $e) {
            $message = ERROR_PREFIX . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . ERROR_CONFIG_UPDATE;
            $this->error_hander->print_error($this->logger, $message);
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
        } catch (\Exception $e) {
            $message =  ERROR_PREFIX . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n" . ERROR_RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            die;
        }

        $json = json_decode($file, true);
        
        if (!json) {
            $message = ERROR_PREFIX . ERROR_JSON_DECODING . ERROR_RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            die;
        }

        switch (strtolower($type)) {
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
                $message = ERROR_PREFIX . ERROR_QUERY_SELECT . ERROR_RECONFIG_REQUIRED;
                $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
                die;
        }
    }

    public function get_locale(string $package, string $lang_file, string $lang, string $section) : array
    {
        switch (strtolower($package)) {
            case 'cms':
                $file = "/locale/CMS/" . $lang_file . ".json";
                break;
            case 'theme':
                $theme = $this->get_main_config()['theme'];
                $file = "/themes/" . $theme . "/locale/" . $lang_file . ".json";
                break;
        }

        try {
            $file = $this->read_file($file);
        } catch (\Exception $e) {
            $message =  ERROR_PREFIX . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n" . ERROR_LOCALE_NOT_FOUND . ERROR_RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            die;
        }
    
        $json = json_decode($file, true);

        if (!json) {
            $message = ERROR_PREFIX . ERROR_JSON_DECODING . ERROR_RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            die;
        }

        $json = $json[$lang];
        var_dump($json);

        switch(strtolower($section)) {
            case 'errors':
                return $json['errors'];
            case 'info':
                return $json['info'];
            case 'user':
                return $json['user'];
            case 'panel':
                return $json['panel'];
            default:
                $message = ERROR_PREFIX . ERROR_LOCALE_NOT_FOUND . ERROR_RECONFIG_REQUIRED;
                $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
                die;
        }

    }
}
