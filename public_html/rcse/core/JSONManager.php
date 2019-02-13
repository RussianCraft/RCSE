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
if (defined("DEBUG") === false) {
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

    public function jsonGetData(string $type, array $params, bool $log = true)
    {
        $type = strtolower($type);

        switch ($type) {
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
                $this->error_handler->print_error_and_redirect($this->logger, "critical", $this->error_msg['Not_defined_datatype'] . $type . "!\n\r", "admin");
                return false;
        }

        $this->logger->write_to_log($message, "info");
        
        
        switch ($type) {
            case "main":
            case "query":
            case "module":
                if (check_data_json($json, $params['entry'])) {
                    $result = $json[$params['entry']];
                } else {
                    return false;
                }
                break;
            case "locale":
                if (check_data_json($json, $params['lang'])) {
                    $json = $json[$params['lang']];
    
                    if (check_data_json($json, $params['entry'])) {
                        $result = $json[$params['entry']];
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
                break;
            case "usergroup":
            case "words":
            case "sections":
            case "bans":
                if ($params['all'] === true) {
                    return $json;
                } elseif (check_data_json($json, $params['entry'])) {
                    $result = $json[$params['entry']];
                } else {
                    return false;
                }
                break;
            default:
        }

        if ($log === true) {
            $this->logger->write_to_log($this->log_msg['Success'], "info");
        }

        return $result;
    }

    public function jsonObtainMainConfig(string $type)
    {
        $path = "/config/main.json";
        $types = ["site", "database"];

        return $this->jsonObtainDataSimple($type, $path, $types);
    }

    public function jsonUpdateMainConfig(string $type, array $contents) : bool
    {
        $path = "/config/main.json";
        $types = ["site", "database"];

        return $this->jsonUpdateDataSimple($type, $path, $types, $contents);
    }

    public function jsonObtainQueries(string $table)
    {
        $path = "/config/queries.json";
        $types = ["accounts", "punishments", "posts", "comments", "topics", "replies"];

        return $this->jsonObtainDataSimple($table, $path, $types);
    }

    public function jsonUpdateQueries(string $table, array $contents) : bool
    {
        $path = "/config/queries.json";
        $types = ["accounts", "punishments", "posts", "comments", "topics", "replies"];

        return $this->jsonUpdateDataSimple($table, $path, $types, $contents);
    }

    public function jsonObtainModuleProps(string $module)
    {
        $path = "/config/modules.json";
        $types = ["dbmanager", "logmanager", "thememanager", "newsletter", "users", "forum", "search", "adminpanel", "papi"];

        return $this->jsonObtainDataSimple($module, $path, $types);
    }

    public function jsonUpdateModuleProps(string $module, array $contents) : bool
    {
        $path = "/config/modules.json";
        $types = ["dbmanager", "logmanager", "thememanager", "newsletter", "users", "forum", "search", "adminpanel", "papi"];
        
        return $this->jsonUpdateDataSimple($module, $path, $types, $contents);
    }

    public function jsonObtainDataSimple(string $type, string $path, array $types)
    {
        $type = strtolower($type);

        if (defined("LOG")) {
            $this->logger->write_to_log("Obtaining data from "+ $path +".\n\r", "info");
        }

        if ($this->compareType($type, $types) === false) {
            $this->error_handler->print_error($this->logger, "fatal", "Selected data type doesn't exist!\n\r");
            return false;
        }

        try {
            $result = $this->jsonObtainAndCheckData($path, $type);
        } catch (\Exception $e) {
            $this->error_handler->print_error($this->logger, "fatal", $e->getMessage());
            return false;
        }

        if (defined("LOG")) {
            $this->logger->write_to_log("Data successfully obtained.\n\r", "info");
        }

        return $result;
    }

    protected function jsonUpdateDataSimple(string $type, string $path, array $types, array $contents) : bool
    {
        $type = strtolower($type);

        $this->logger->write_to_log("Updating config for "+ $type +".\n\r", "info");

        if ($this->compareType($type, $types) === false) {
            $this->error_handler->print_error($this->logger, "fatal", "Selected config type doesn't exist!\n\r");
            return false;
        }

        $json = $this->jsonReadAndParseData($path);

        foreach ($json[$type] as $key => $value) {
            $json[$type][$key] = $contents[$key];
        }

        if ($this->jsonParseAndWriteData($path, $json)) {
            $this->error_handler->print_error($this->logger, "fatal", "Failed to parse or write the data!\n\r");
            return false;
        }

        $this->logger->write("Config update successful!\n\r", "info");

        return true;
    }

    protected function jsonObtainAndCheckData(string $path, string $entry)
    {
        
        if (defined("LOG")) {
            $this->logger->write_to_log("Tying to obtain data from file ". $path .".\n\r", "info");
        }

        try {
            $json = $this->jsonReadAndParseData($path);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        if ($this->jsonCheckData($json, $entry)) {
            return $json[$entry];
        } else {
            throw new \Exception("Failed to obtain data for ". $entry ."!\n\r", 06);
        }
    }

    protected function jsonReadAndParseData(string $path) : array
    {
        try {
            $file = $this->file_handler->read_file($path, $log);
        } catch (\Exception $e) {
            $message = $this->error_prefix . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "critical", $message);
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $json = json_decode($file, true);

        if ($json === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_DECODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "critical", $message);
            throw new \Exception("Failed to decode json!\n\r", 05);
        }

        return $json;
    }

    protected function jsonParseAndWriteData(string $path, string $json) : bool
    {
        $json_result = json_encode($json);

        if ($json_result === false) {
            $message = ERROR_PREFIX_JSON . ERROR_JSON_ENCODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        try {
            $this->file_handler->write_file($path, $json_result);
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . ERROR_CONFIG_UPDATE . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        return true;
    }

    protected function jsonCheckData(string $json, string $data) : bool
    {
        if (array_key_exists($data, $json) === false) {
            $message = ERROR_PREFIX_JSON . ERROR_INCORRECT_CONFIG_TYPE . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, $message);
            return false;
        }
        return true;
    }

    protected function compareType(string $type, array $variants) : bool
    {
        $type = strtolower($type);

        foreach ($variants as $value) {
            $value = strtolower($value);
        }
        unset($value);

        if (array_search($type, $variants) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Writes $contents of selected $type to modules.json, also checks $key values of $contents to correspond to previous module.json content,
     * if does not match prints error and redirects to admin panel.
     *
     * @param string $type Module
     * @param array $contents Data to write
     * @return bool In case of success returns true
     */
    /*public function set_modules(string $type, array $contents) : bool
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
    }*/
    
    /**
     * Writes $contents of selected $type to usergroups.json, also checks $key values of $contents to correspond to previous usergroups.json content,
     * if does not match prints error and redirects to admin panel.
     *
     * @param string $type Usergoup
     * @param array $contents Data to write
     * @return bool In case of success returns true
     */
    /*public function set_usergroups(string $type, array $contents) : bool
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
    }*/

    /**
     * Undocumented function
     *
     * @param string $group Usergroup to remove
     * @return boolean  If succeeds, true
     */
    /*public function remove_usergroup(string $group) : bool
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
    }*/
}
