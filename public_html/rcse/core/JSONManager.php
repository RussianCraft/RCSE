<?php
declare(strict_types=1);
namespace RCSE\Core;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}
if (defined("RECONFIG_REQUIRED") === false) {
    define("RECONFIG_REQUIRED", "Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n\r");
}
if (defined("REPORT_ERROR") === false) {
    define("REPORT_ERROR", "Check your source code or send this message (with error) to Issues at GitHub!\n\r");
}
if(defined("DEBUG") === false) {
    define("DEBUG", false);
}

/**
 * JSONManager
 * Parses from and to JSON config files
 */
class JSONManager
{
    private $logger;
    private $error_handler;
    private $file_handler;
    private $error_prefix = "JSONManager Error: ";
    private $error_msg = [
        "JSON_Decoding" => "Tried to decode parameters from JSON fomat, but failed!\n\r",
        "JSON_Encoding" => "Tried to encode parameters to JSON fomat, but failed!\n\r",
        "Entry_dont_exist" => "Selected entry does not exist!\n\r",
        "Incorrect_config_type" => "Incorrect config type or not selected!\n\r",
        "Wrong_config_structure" => "Tried to write config, but structure does not match!\n\r",
        "Config_update_failed" => "Tried to write new config data, but failed!\n\r",
        "Locale_file_not_found" => "Selected locale file not found!\n\r",
        "Lang_not_found" => "Selected language not found in file!\n\r",
        "Module_props_not_found" => "Properties for selected module not found!\n\r",
        "Module_props_update_failed" => "Tried to write new module properties, but failed!\n\r",
        "Query_not_found" => "Selected query not found!\n\r",
        "Query_group_not_found" => "Selected query group not found!\n\r",
        "Usergroup_not_found" => "Selected usergoup not found!\n\r",
        "Usergroup_remove_failed" => "Tried to remove usergroup, but failed!\n\r",
        "Usergroup_update_failed" => "Tried to update usergroup, but failed!\n\r",
        "Forbidden_words_not_found" => "Word section not found!\n\r"
    ];
    
    private $log_msg = [
        "Obtaining_config" => "Obtaining main config.\n\r",
        "Obtaining_query_group" => "Obtaining query group ",
        "Obtaining_query" => "Obtaining query ",
        "Obtaining_module" => "Obtaining data for module",
        "Obtaining_locale" => "Obtaining locale data for ",
        "Obtaining_usergroup" => "Obtaining usergroup ",
        "Obtaining_words" => "Obtaining forbidden words ",
        "Obtaining_section" => "Obtaining forum section ",
        "Success" => "Data obtained successfully!\n\r"
    ];
 
    public function __construct()
    {
        $this->file_handler = new Handlers\FileHandler();
        $this->logger = new LogManager(get_class($this), $this);
        $this->error_handler = new Handlers\ErrorHandler();
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

        if ($log && DEBUG) {
            $this->logger->write_to_log("Reading file: $file_path!\n", "debug");
        }

        if (is_readable($file_path) === false) {
            if ($log && DEBUG) {
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

        if ($log && DEBUG) {
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

        if (DEBUG) {
            $this->logger->write_to_log("Writing to file: $file_path!\n", "debug");
        }

        if (is_writeable($file_path) === false) {
            if (DEBUG) {
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

        if (DEBUG) {
            $this->logger->write_to_log("File written!\n", "debug");
        }

        return true;
    }

    /************************************/
    /*      new functions, MUST be done      */
    /************************************/


    public function get_data_json(string $type, array $params, bool $log = true)
    {
        $type = strtolower($type);

        switch($type) {
            case "main":
                $path = "/configs/main.json";
                $message = $this->log_msg['Obtaining_config'];
                $error_not_found = $this->error_msg['Incorrect_config_type'];
                break;
            case "query":
                $path = "/configs/queries.json";
                $message = $this->log_msg['Obtaining_query'] ."(".$params['entry'].").\n\r";
                $error_not_found = $this->error_msg['Query_group_not_found'];
                break;
            case "module":
                $path = "/configs/modules.json";
                $message = $this->log_msg['Obtaining_module'] ."(".$params['entry'].").\n\r";
                $error_not_found = $this->error_msg['Module_props_not_found'];
                break;
            case "locale":
                $path = "/resources/locale/". $params['source'] ."/lang.json";
                $message = $this->log_msg['Obtaining_locale'] ."(".$params['entry'].").\n\r";
                $error_not_found = $this->error_msg['Locale_file_not_found'];
                break;
            case "usergroup":
                $path = "/configs/usergroups.json";
                $message = $this->log_msg['Obtaining_usergroup'] ."(".$params['entry'].").\n\r";
                $error_not_found = $this->error_msg['Usergroup_not_found'];
                break;
            case "words":
                $path = "/configs/forbidden_words.json";
                $message = $this->log_msg['Obtaining_words'] ."(".$params['entry'].").\n\r";
                $error_not_found = $this->error_msg['Forbidden_words_not_found'];
                break;
            case "sections":
                $path = "/configs/forum_sections.json";
                $message = $this->log_msg['Obtaining_section'] . "(" .$params['entry']. ").\n\r";
                $error_not_found = $this->error_msg['Forum_section_not_found'];
                break;
            case "bans":
                $path = "/configs/ban_types.json";
                $message = $this->log_msg['Obtaining_ban'] . "(" .$params['entry']. ").\n\r";
                $error_not_found = $this->error_msg['Ban_type_not_found'];
                break;
            default:
                $message = $this->error_msg['Not_defined_datatype'] . $type . "!\n\r";
                $error = true;
        }

        if($log === true) {
            $this->logger->write_to_log($message, "info");
        }

        if($error === true) {
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        try {
            $file = $this->file_handler->read_file($path, $log);
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        $json = json_decode($file, true);

        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        switch($type) {
            case "main":
            case "query":
            case "module":
                if (array_key_exists($params['entry'], $json) === false) {
                    $message = ERROR_PREFIX_JSON . ERROR_INCORRECT_CONFIG_TYPE . REPORT_ERROR . RECONFIG_REQUIRED;
                    $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
                    return false;
                } else {
                    $result = $json[$params['entry']];
                }
            case "locale":
                if (array_key_exists($params['lang'], $json) === false) {
                    $message = ERROR_PREFIX_JSON . ERROR_LOCALE_LANG . REPORT_ERROR . RECONFIG_REQUIRED;
                    $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
                    return false;
                } else {    
                    $json = $json[$params['lang']];
    
                    if (array_key_exists($params['entry'], $json) === false) {
                        $message = ERROR_PREFIX_JSON . ERROR_LOCALE_NOT_FOUND . REPORT_ERROR . RECONFIG_REQUIRED;
                        $this->error_handler->print_error_and_redirect($this->logger, $message, "admin");
                        return false;
                    } else {
                        $result = $json[$params['entry']];
                    }
                }
            
            case "usergroup":
            case "words":
            case "sections":
            case "bans":
                if($params['all'] === true) {
                    return $json;
                } else {
                    if (array_key_exists($params['entry'], $json) === false) {
                        $message = ERROR_PREFIX_JSON . ERROR_INCORRECT_CONFIG_TYPE . REPORT_ERROR . RECONFIG_REQUIRED;
                        $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
                        return false;
                    } else {
                        $result = $json[$params['entry']];
                    }
                }
        }

        if ($log === true) {
            $this->logger->write_to_log($log_msg['Success'], "info");
        }

        return $json[$type];

    }

    public function set_data_json() : bool
    {}
    


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
            $file = $this->file_handler->read_file("/configs/main.json", $log);
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
