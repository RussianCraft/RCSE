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
define("ERROR_PREFIX_JSON", "JSONManager Error: ");
define("ERROR_JSON_DECODING", "Tried to decode parameters from JSON fomat, but failed!\n");
define("ERROR_JSON_ENCODING", "Tried to encode parameters to JSON fomat, but failed!\n");
define("ERROR_ENTRY_DOENT_EXIST", "Selected entry may not exist!\n");
define("ERROR_INCORRECT_CONFIG_TYPE", "Incorrect config type or not selected!\n");
define("ERROR_CONFIG_STRUCTURE", "Tried to write config, but structure does not match!\n");
define("ERROR_CONFIG_UPDATE", "Tried to write new config data, but failed!\n");
define("ERROR_LOCALE_NOT_FOUND", "Selected locale not found or incorrect!\n");
define("ERROR_LOCALE_LANG", "Selected language not found in file!\n");
define("ERROR_MODULE_ENTRY", "Selected module not found!\n");
define("ERROR_QUERY_SELECT", "Selected query not found!\n");
define("ERROR_USERGROUP_NF", "Selected usergoup not found!\n");
define("ERROR_USERGROUP_REMOVE", "Failed to remove selected user group! ");

/**
 * JSONManager
 * Parses from and to JSON config files
 */
class JSONManager
{
    private $logger;
    private $error_handler;
    private $debug;
 
    public function __construct()
    {
        $this->logger = new LogManager(get_class($this), $this);
        $this->error_handler = new Handlers\ErrorHandler();
        $this->debug = $this->get_main_config()["debug"];
    }

    /**
     * Reads contents of /$file(i.e. /configs/main.json), if file not present or not readable throws FileNotFoundException,
     * if present, but locked throws FileLockException
     *
     * @param string $file Filename, must end with .json (i.e. "main.json)
     * @throws Exceptions\FileNotFoundException
     * @throws Exception\FileLockException
     * @return string Contents of the file
     */
    private function read_file(string $file, bool $log = true) : string
    {
        $file_handler;
        $file_contents;
        $file_path = ROOT . $file;

        if ($log && $this->debug) {
            $this->logger->write_to_log("Reading file: $file_path!\n", "debug");
        }

        if (is_readable($file_path) === false) {
            if ($log && $this->debug) {
                $this->logger->write_to_log("FIle is not readable! Trying chmod(0766)!\n", "notice");
            }
            chmod($file_path, 0766);
            if (is_readable($file_path) === false) {
                throw new Exceptions\FileNotFoundException($file_path);
            }
        }

        $file_handler = fopen($file_path, "rb");

        if (flock($file_handler, LOCK_SH, $eWouldBlock) === false || $eWouldBlock) {
            fclose($file_handler);
            throw new Exceptions\FileLockException($file_path);
        }
        
        $file_contents = fread($file_handler, filesize($file_path));
    
        flock($file_handler, LOCK_UN);
        fclose($file_handler);

        if ($log && $this->debug) {
            $this->logger->write_to_log("File read!\n", "debug");
        }

        return $file_contents;
    }

    /**
     * Writes $contents to /$file (i.e. /configs/main.json), if file not present or not writeable throws FileNotFoundException,
     *  if writing failed throws FileWriteException, if succeeds returns true
     *
     * @param string $file Filename, must end with .json (i.e. "main.json")
     * @param string $contents Data to write
     * @throws Exceptions\FileNotFoundException
     * @throws Exceptions\FileWriteException
     * @return boolean True in case of success
     */
    private function write_file(string $file, string $contents) : bool
    {
        $file_handler;
        $file_path = ROOT . $file;

        if ($this->debug) {
            $this->logger->write_to_log("Writing to file: $file_path!\n", "debug");
        }

        if (is_writeable($file_path) === false) {
            if ($this->debug) {
                $this->logger->write_to_log("FIle is not writeable! Trying chmod(0766)!\n", "notice");
            }

            chmod($file_path, 0766);
            if (is_writeable($file_path) === false) {
                throw new Exceptions\FileNotFoundException($file_path);
            }
        }

        $file_handler = fopen($file_path, "wb");

        while (flock($file_handler, LOCK_EX, $eWouldBlock) === false) {
        }

        if (fwrite($file_handler, $contents) === false) {
            fclose($file_handler);
            throw new Exceptions\FileWriteException($file_path);
        }

        flock($file_handler, LOCK_UN);
        fclose($log_handler);

        if ($this->debug) {
            $this->logger->write_to_log("File written!\n", "debug");
        }

        return true;
    }

    /************************************/
    /*      new functions, MUST be done      */
    /************************************/


public function get_data_json() : array {}

public function set_data_json() : array {}
    


    /**
     * Reads main.json and parses it, if failed echoes "File not found" and redirects to admin.php to reconfigure,
     * if succeeds returns array with chosen properties
     *
     * @param string $type Config type, defalut "site"
     * @return array Array of config properties
     */
    public function get_main_config(string $type="site", bool $log = true)
    {
        $type = strtolower($type);
        

        if ($log) {
            $this->logger->write_to_log("Acquiring config ($type)!\n", "info");
        }

        try {
            if ($log) {
                $file = $this->read_file("/configs/main.json");
            } else {
                $file = $this->read_file("/configs/main.json", false);
            }
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        $json = json_decode($file, true);

        if ($json == false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        if (array_key_exists($type, $json) === false) {
            $message = ERROR_PREFIX_JSON . ERROR_INCORRECT_CONFIG_TYPE . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        if ($log) {
            $this->logger->write_to_log("Config acquired!\n", "info");
        }
        return $json[$type];
    }
    
    /**
     * Writes $contents of selected $type to main.json, also checks $key values of $contents to correspond to previous main.json content,
     * if does not match prints error and redirects to admin panel.
     *
     * @param string $type Config type
     * @param array $contents Data to write
     * @return bool In case of success returns true
     */
    public function set_main_config(string $type, array $contents) : bool
    {
        $type = strtolower($type);

        $this->logger->write_to_log("Writing new config ($type)!\n", "info");

        try {
            $file = $this->read_file("/configs/main.json");
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $json_orig = json_decode($file, true);
        
        if ($json_orig === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }
        
        
        foreach ($json_orig[$type] as $key => $value) {
            $json_orig[$type][$key] = $contents[$key];
        }

        $json = json_encode($json_orig);

        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_ENCODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        try {
            $this->write_file("/configs/main.json", $json);
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . ERROR_CONFIG_UPDATE . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        $this->logger->write_to_log("Config written!\n", "info");

        return true;
    }
    
    /**
     * Reads queries.json and parses it, if failed echoes "File not found" and redirects to admin.php to reconfigure,
     * if succeeds returns array with chosen queries
     *
     * @param string $type Query type
     * @return array Array of queries
     */
    public function get_queries(string $type)
    {
        $type = strtolower($type);

        $this->logger->write_to_log("Acquiring quieries ($type)!\n", "info");

        try {
            $file = $this->read_file("/configs/queries.json");
        } catch (\Exception $e) {
            $message =  ERROR_PREFIX_JSON . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $json = json_decode($file, true);
        
        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        if (array_key_exists($type, $json) === false) {
            $message = ERROR_PREFIX_JSON . ERROR_QUERY_SELECT . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $this->logger->write_to_log("Queries acquired!\n", "info");

        return $json[$type];
    }

    /**
     *  Reads selected $file from $package (i.e. "CMS" for package and "common.json" for file makes "/locale/CMS/common.json"),
     * and loads $section of text lines for selected $lang
     *
     * @param string $package Source for lang file
     * @param string $file File to load
     * @param string $lang Language to seek
     * @param string $type Needed section (i.e. 'errors')
     * @return array
     */
    public function get_locale(string $package, string $file, string $lang, string $type)
    {
        $type = strtolower($type);

        $this->logger->write_to_log("Acquiring locale ($type)!\n", "info");

        switch (strtolower($package)) {
            case 'cms':
                $file = "/locale/CMS/" . $file . ".json";
                break;
            case 'theme':
                $theme = $this->get_main_config()['theme'];
                $file = "/themes/" . $theme . "/locale/" . $file . ".json";
                break;
        }

        try {
            $file = $this->read_file($file);
        } catch (\Exception $e) {
            $message =  ERROR_PREFIX_JSON . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n" . ERROR_LOCALE_NOT_FOUND . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }
    
        $json = json_decode($file, true);

        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        if (array_key_exists($lang, $json) === false) {
            $message = ERROR_PREFIX_JSON . ERROR_LOCALE_LANG . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $json = $json[$lang];

        if (array_key_exists($type, $json) === false) {
            $message = ERROR_PREFIX_JSON . ERROR_LOCALE_NOT_FOUND . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $this->logger->write_to_log("Locale acquired!\n", "info");
 
        return $json[$type];
    }

    /**
     * Reads modules.json and parses it, if failed echoes "File not found" and redirects to admin.php to reconfigure,
     * if succeeds returns array with chosen properties
     *
     * @param string $type Required module
     * @return array Module's properties
     */
    public function get_modules_properties(string $type)
    {
        $type = strtolower($type);

        if ($type !== "logmanager") {
            $this->logger->write_to_log("Acquiring module ($type) properties!\n", "info");
        }
        try {
            if ($type !== "logmanager") {
                $file = $this->read_file("/configs/modules.json");
            } else  {
                $file = $this->read_file("/configs/modules.json", false);
            }
        } catch (\Exception $e) {
            $message =  ERROR_PREFIX_JSON . "(" . $e->getCode() . ") " . $e->getMessage() . "!\n". REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $json = json_decode($file, true);
        
        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        if (array_key_exists($type, $json) === false) {
            $message = ERROR_PREFIX_JSON . ERROR_MODULE_ENTRY . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        if ($type !== "logmanager") {
            $this->logger->write_to_log("Module properties acquired!\n", "info");
        }
        return $json[$type];
    }
    
    /**
     * Writes $contents of selected $type to modules.json, also checks $key values of $contents to correspond to previous module.json content,
     * if does not match prints error and redirects to admin panel.
     *
     * @param string $type Module
     * @param array $contents Data to write
     * @return bool In case of success returns true
     */
    public function set_modules(string $type, array $contents) : bool
    {
        $type = strtolower($type);

        $this->logger->write_to_log("Writing module ($type) properties!\n", "info");

        try {
            $file = $this->read_file("/configs/modules.json");
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $json_orig = json_decode($file, true);
        
        if ($json_orig === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }
        
        
        foreach ($json_orig[$type] as $key => $value) {
            $json_orig[$type][$key] = $contents[$key];
        }

        $json = json_encode($json_orig);

        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_ENCODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        try {
            $this->write_file("/configs/modules.json", $json);
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . ERROR_CONFIG_UPDATE . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        $this->logger->write_to_log("Module properties written!\n", "info");

        return true;
    }

    /**
     * Reads usergourps.json and parses it, if failed echoes "File not found" and redirects to admin.php to reconfigure,
     * if succeeds returns array with chosen properties
     *
     * @param string $type Required usergroup, if "all" selected, outputs every existing group, "all" is default
     * @return array Usergroup data array
     */
    public function get_usergroups(string $type="all")
    {
        $type = strtolower($type);

        $this->logger->write_to_log("Acquiring usergroup ($type) data!\n", "info");

        try {
            $file = $this->read_file("/configs/usergroups.json");
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $json = json_decode($file, true);
        
        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        if ($type === "all") {
            return $json;
        }

        if (array_key_exists($type, $json) === false) {
            $message = ERROR_PREFIX_JSON . ERROR_USERGROUP_NF . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $this->logger->write_to_log("Usergroup data acquired!\n", "info");

        return $json[$type];
    }
    /**
     * Writes $contents of selected $type to usergroups.json, also checks $key values of $contents to correspond to previous usergroups.json content,
     * if does not match prints error and redirects to admin panel.
     *
     * @param string $type Usergoup
     * @param array $contents Data to write
     * @return bool In case of success returns true
     */
    public function set_usergroups(string $type, array $contents) : bool
    {
        $type = strtolower($type);

        $this->logger->write_to_log("Writing usergroup ($type) data!\n", "info");

        try {
            $file = $this->read_file("/configs/usergroups.json");
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
            return false;
        }

        $json_orig = json_decode($file, true);
        
        if ($json_orig === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }
        
        if (array_key_exists($type, $json_orig) === false) {
            $json_orig[$type] = $contents;
        } else {
            foreach ($json_orig[$type] as $key => $value) {
                $json_orig[$type][$key] = $contents[$key];
            }
        }

        $json = json_encode($json_orig);

        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_ENCODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        try {
            $this->write_file("/configs/usergroups.json", $json);
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . ERROR_CONFIG_UPDATE . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        $this->logger->write_to_log("Usergroup data written!\n", "info");

        return true;
    }

    /**
     * Undocumented function
     *
     * @param string $group Usergroup to remove
     * @return boolean  If succeeds, true
     */
    public function remove_usergroup(string $group) : bool
    {
        $type = strtolower($group);

        $this->logger->write_to_log("Removing usergroup ($type) data!\n", "info");

        try {
            $file = $this->read_file("/configs/usergroups.json");
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, "", $message, "admin");
            return false;
        }

        $json_orig = json_decode($file, true);
        
        if ($json_orig === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        if (array_key_exists($type, $json_orig) === false) {
            $message = ERROR_PREFIX_JSON . ERROR_USERGROUP_REMOVE . ERROR_USERGROUP_NF . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, $error);
            return false;
        }
        
        unset($json_orig[$type]);
        $json = json_encode($json_orig);

        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_ENCODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        try {
            $this->write_file("/configs/usergroups.json", $json);
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . ERROR_CONFIG_UPDATE . REPORT_ERROR;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }

        $this->logger->write_to_log("Usergroup data removed!\n", "info");

        return true;
    }
}
