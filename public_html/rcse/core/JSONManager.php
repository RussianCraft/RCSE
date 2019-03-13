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

    private $config_path = [
        "main" => "/config/main.json",
        "modules" => "/config/modules.json",
        "queries" => "/config/queries.json",
        "forum_sections" => "/config/forum_sections.json",
        "ban_types" => "/config/ban_types.json",
        "forbidden_words" => "/config/forbidden_words.json",
        "usergroups" => "/config/usergroups.json",
        "menu" => "/config/menu.json"
    ];

    public function __construct()
    {
        $this->file_handler = new Handlers\FileHandler();
        $this->logger = new LogManager(get_class($this), $this);
        $this->error_handler = new Handlers\ErrorHandler();
    }

    public function jsonObtainMainConfig(string $type)
    {
        $types = ["site", "database"];

        return $this->jsonObtainDataSimple($type, $this->config_path["main"], $types);
    }

    public function jsonUpdateMainConfig(string $type, array $contents) : bool
    {
        $types = ["site", "database"];

        return $this->jsonUpdateDataSimple($type, $this->config_path["main"], $types, $contents);
    }

    public function jsonObtainQueries(string $table)
    {
        $types = ["accounts", "punishments", "posts", "comments", "topics", "replies"];

        return $this->jsonObtainDataSimple($table, $this->config_path["queries"], $types);
    }

    public function jsonObtainModuleProps(string $module)
    {
        $types = ["dbmanager", "logmanager", "thememanager", "newsletter", "users", "forum", "search", "adminpanel", "papi"];

        return $this->jsonObtainDataSimple($module, $this->config_path["modules"], $types);
    }

    public function jsonUpdateModuleProps(string $module, array $contents) : bool
    {
        $types = ["dbmanager", "logmanager", "thememanager", "newsletter", "users", "forum", "search", "adminpanel", "papi"];
        
        return $this->jsonUpdateDataSimple($module, $this->config_path["modules"], $types, $contents);
    }

    public function jsonObtainLocale(string $lang, string $element, string $source)
    {
        $path = "/resources/locale/". $source ."/lang.json";
        $types = ["errors", "info", "panel", "user"];

        return $this->jsonObtainDataDouble($lang, $element, $path, $types);
    }

    public function jsonObtainUsergroup(string $group)
    {
        return $this->jsonObtainDataAllNSmall($group, $this->config_path["usergroups"], []);
    }

    public function jsonUpdateUsergroup(string $group, array $contents)
    {
        return $this->jsonUpdateDataSimple($group, $this->config_path["usergroups"], [], $contents);
    }

    public function jsonRemoveUsergroup(string $group)
    {
        return $this->jsonRemoveDataSimple($group, $this->config_path["usergroups"]);
    }

    public function jsonObtainWords(string $type)
    {
        $types = ["login", "swears"];

        return $this->jsonObtainDataAllNSmall($type, $this->config_path["forbidden_words"], $types);
    }

    public function jsonUpdateWords(string $type, array $contents)
    {
        $types = ["login", "swears"];

        return $this->jsonUpdateDataSimple($type, $this->config_path["forbidden_words"], $types, $contents);
    }

    public function jsonObtainSection(string $type)
    {
        return $this->jsonObtainDataAllNSmall($type, $this->config_path["forum_sections"], []);
    }

    public function jsonUpdateSection(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["forum_sections"], [], $contents);
    }

    public function jsonRemoveSection(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["forum_sections"]);
    }

    public function jsonObtainBan(string $type)
    {
        return $this->jsonObtainDataAllNSmall($type, $this->config_path["ban_types"], []);
    }

    public function jsonUpdateBan(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["ban_types"], [], $contents);
    }

    public function jsonRemoveBan(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["ban_types"]);
    }
    
    public function jsonObtainMenu(string $type)
    {
        return $this->jsonObtainDataAllNSmall($type, $this->config_path["menu"], []);
    }

    public function jsonUpdateMenu(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["menu"], [], $contents);
    }

    public function jsonRemoveFromMenu(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["menu"]);
    }


    protected function jsonObtainDataAllNSmall(string $type, string $path, array $types)
    {
        if ($type === "all") {
            try {
                return $this->jsonReadAndParseData($path);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        } else {
            return $this->jsonObtainDataSimple($type, $path, $types);
        }
    }

    protected function jsonObtainDataDouble(string $type1, string $type2, string $path, array $types)
    {
        $type2 = strtolower($type2);

        $json = $this->jsonObtainDataSimple($type1, $path, []);

        if ($json === false) {
            $this->error_handler->print_error($this->logger, "fatal", "Failed to obtain data from ". $path ."!\n\r");
            return false;
        }

        if ($this->compareType($type2, $types) === false) {
            $this->error_handler->print_error($this->logger, "fatal", "Selected data type doesn't exist!\n\r");
            return false;
        }

        return $json[$type2];
    }

    protected function jsonObtainDataSimple(string $type, string $path, array $types)
    {
        $type = strtolower($type);

        if (defined("DEBUG")) {
            $this->logger->write_to_log("Obtaining data from "+ $path +".\n\r", "info");
        }

        if (empty($types) === false) {
            if ($this->compareType($type, $types) === false) {
                $this->error_handler->print_error($this->logger, "fatal", "Selected data type doesn't exist!\n\r");
                return false;
            }
        }

        try {
            $result = $this->jsonObtainAndCheckData($path, $type);
        } catch (\Exception $e) {
            $this->error_handler->print_error($this->logger, "fatal", $e->getMessage());
            return false;
        }

        if (defined("DEBUG")) {
            $this->logger->write_to_log("Data successfully obtained.\n\r", "info");
        }

        return $result;
    }

    protected function jsonUpdateDataSimple(string $type, string $path, array $types, array $contents) : bool
    {
        $type = strtolower($type);

        
        if (defined("DEBUG")) {
            $this->logger->write_to_log("Updating config for ". $type .".\n\r", "info");
        }

        if (empty($types) === false) {
            if ($this->compareType($type, $types) === false) {
                $this->error_handler->print_error($this->logger, "fatal", "Selected data type doesn't exist!\n\r");
                return false;
            }
        }

        $json = $this->jsonReadAndParseData($path);

        if(empty($json[$type]) === false) {
            foreach ($json[$type] as $key => $value) {
                $json[$type][$key] = $contents[$key];
            }
        } else {
            $json[$type] = $contents;
        }


        if ($this->jsonParseAndWriteData($path, $json) === false) {
            $this->error_handler->print_error($this->logger, "fatal", "Failed to parse or write the data!\n\r");
            return false;
        }

        
        if (defined("DEBUG")) {
            $this->logger->write("Config update successful!\n\r", "info");
        }

        return true;
    }

    protected function jsonRemoveDataSimple(string $type, string $path) : bool
    {
        $type = strtolower($type);

        if (defined("DEBUG")) {
            $this->logger->write_to_log("Removing data " . $type . ".\n\r", "info");
        }

        $json = $this->jsonReadAndParseData($path);

        if (array_key_exists($type, $json) === false) {
            $this->error_handler->print_error($this->logger, "error", "Failed to remove ". $type ."!\n\r");
            return false;
        }

        unset($json[$type]);

        if ($this->jsonParseAndWriteData($path, $json) === false) {
            $this->error_handler->print_error($this->logger, "fatal", "Failed to parse or write the data!\n\r");
            return false;
        }

        if (defined("DEBUG")) {
            $this->logger->write("Data removed successfuly.\n\r", "info");
        }

        return true;
    }

    protected function jsonObtainAndCheckData(string $path, string $entry)
    {
        if (defined("DEBUG")) {
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
            $file = $this->file_handler->read_file($path);
        } catch (\Exception $e) {
            $message = $this->error_prefix . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "critical", $message);
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $json = json_decode($file, true);

        if ($json === false || $json === null) {
            $message = $this->error_prefix . $this->error_msg["JSON_Decoding"] . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "critical", $message);
            throw new \Exception("Failed to decode json!\n\r", 05);
        }

        return $json;
    }

    protected function jsonParseAndWriteData(string $path, array $json) : bool
    {
        $json_result = json_encode($json);

        if ($json_result === false) {
            $message = $this->error_prefix . ERROR_JSON_ENCODING . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        try {
            $this->file_handler->write_file($path, $json_result);
        } catch (\Exception $e) {
            $message = $this->error_prefix . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . ERROR_CONFIG_UPDATE . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "critical", $message);
            return false;
        }

        return true;
    }

    protected function jsonCheckData(array $json, string $data) : bool
    {
        if (array_key_exists($data, $json) === false) {
            $message = $this->error_prefix . $this->error_msg["Incorrect_config_type"] . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error($this->logger, "fatal", $message);
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
}