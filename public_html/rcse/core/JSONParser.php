<?php
declare(strict_types=1);
namespace RCSE\Core;

/**
 * JSONParser
 * Parses from and to JSON config files
 */
class JSONParser
{
    private $logger;
    private $file_handler;

    public function __construct()
    {
        $this->file_handler = new Handlers\FileHandler();
        $this->logger = new Logger();
    }

    protected function jsonObtainDataAllNSmall(string $type, string $file_dir, string $file_name, array $types)
    {
        $type = strtolower($type);

        if ($type === "all") {
            try {
                return $this->jsonReadAndParseData($file_dir, $file_name);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        } else {
            try {
                return $this->jsonObtainDataSimple($type, $file_dir, $file_name, $types);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }
    }

    protected function jsonObtainDataDouble(string $type1, string $type2, string $file_dir, string $file_name, array $types)
    {
        $type1 = strtolower($type1);
        $type2 = strtolower($type2);

        try {
            $json = $this->jsonObtainDataSimple($type1, $file_dir, $file_name, []);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        if (empty($types) === false) {
            if ($this->compareType($type2, $types) === false) {
                throw new \Exception("Chosen key ({$type2}) doesn't exist in file {$file_name}.", 1014);
            }
        }

        return $json[$type2];
    }

    protected function jsonObtainDataSimple(string $type, string $file_dir, string $file_name, array $types)
    {
        $type = strtolower($type);

        if (empty($types) === false) {
            if ($this->compareType($type, $types) === false) {
                throw new \Exception("Chosen key ({$type}) doesn't exist in file {$file_name}.", 1014);
            }
        }

        try {
            $result = $this->jsonObtainAndCheckData($file_dir, $file_name, $type);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return $result;
    }

    protected function jsonUpdateDataSimple(string $type, string $file_dir, string $file_name, array $types, array $contents) : bool
    {
        $type = strtolower($type);

        $this->logger->log($this->logger::INFO, "Updating data ({$type}) in file {$file_name}.", get_class($this));

        if (empty($types) === false) {
            if ($this->compareType($type, $types) === false) {
                throw new \Exception("Chosen key ({$type}) doesn't exist in file {$file_name}.", 1014);
            }
        }

        try {
            $json = $this->jsonReadAndParseData($file_dir, $file_name);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        if (empty($json[$type]) === false) {
            foreach ($json[$type] as $key => $value) {
                $json[$type][$key] = $contents[$key];
            }
        } else {
            $json[$type] = $contents;
        }


        try {
            $this->jsonParseAndWriteData($file_dir, $file_name, $json);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $this->logger->log($this->logger::INFO, "Data updated successfully.", get_class($this));
        return true;
    }

    protected function jsonRemoveDataSimple(string $type, string $file_dir, string $file_name) : bool
    {
        $type = strtolower($type);

        $this->logger->log($this->logger::INFO, "Trying to remove data ({$type}) from {$file_name}.", get_class($this));

        try {
            $json = $this->jsonReadAndParseData($file_dir, $file_name);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        if (array_key_exists($type, $json) === false) {
            $this->logger->log($this->logger::WARNING, "Failed to remove data ({$type}): data key not found.", get_class($this));
            return false;
        }

        unset($json[$type]);

        try {
            $this->jsonParseAndWriteData($file_dir, $file_name, $json);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $this->logger->log($this->logger::INFO, "Data removed successfuly.", get_class($this));
        return true;
    }

    protected function jsonObtainAndCheckData(string $file_dir, string $file_name, string $entry)
    {
        $this->logger->log($this->logger::INFO, "Trying to obtain data ({$entry}) from {$file_name}.", get_class($this));

        try {
            $json = $this->jsonReadAndParseData($file_dir, $file_name);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        if ($this->jsonCheckData($json, $entry)) {
            $this->logger->log($this->logger::INFO, "Data obtained successfuly.", get_class($this));
            return $json[$entry];
        } else {
            $this->logger->log($this->logger::ERROR, "Failed to obtain data ({$entry}) from {$file_name}!", get_class($this));
            throw new \Exception("Failed to obtain data for {$entry}!", 1012);
        }
    }

    protected function jsonReadAndParseData(string $file_dir, string $file_name) : array
    {
        try {
            $file = $this->file_handler->fileRead($file_dir, $file_name);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $json = json_decode($file, true);

        if ($json === false || $json === null) {
            throw new \Exception("Failed to decode json!", 1010);
        }

        return $json;
    }

    protected function jsonParseAndWriteData(string $file_dir, string $file_name, array $json) : bool
    {
        $json_result = json_encode($json);

        if ($json_result === false) {
            throw new \Exception("Failed to encode json!", 1011);
        }

        try {
            $this->file_handler->fileWrite($file_dir, $file_name, $json_result);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    protected function jsonCheckData(array $json, string $data) : bool
    {
        if (array_key_exists($data, $json) === false) {
            return false;
        }
        return true;
    }

    protected function compareType(string $type, array $variants) : bool
    {
        $type = strtolower($type);

        foreach ($variants as $key => $value) {
            $variants[$key] = strtolower($value);
        }
        unset($value);

        if (array_search($type, $variants) === false) {
            return false;
        } else {
            return true;
        }
    }
}
