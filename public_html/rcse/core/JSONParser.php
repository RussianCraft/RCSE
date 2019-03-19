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
class JSONParser
{
    private $logger;
    private $error_handler;
    private $file_handler;

    public function __construct()
    {
        $this->file_handler = new Handlers\FileHandler();
        $this->logger = new Logger();
        $this->error_handler = new Handlers\ErrorHandler();
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