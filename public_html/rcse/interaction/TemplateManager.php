<?php
declare(strict_types=1);
namespace RCSE\Interaction;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}
if (defined("RECONFIG_REQUIRED") === false) {
    define("RECONFIG_REQUIRED", "Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n");
}
if (defined("REPORT_ERROR") === false) {
    define("REPORT_ERROR", "Check your source code or send this message (with error) to Issues at GitHub!\n");
}

class TemplateManager
{
    private $data = [];
    private $error_handler;
    private $logger;
    private $config;

    public function __construct()
    {
        $this->config = new \RCSE\Core\JSONManager();
        $this->logger = new \RCSE\Core\LogManager(get_class($this), $this->config);
        $this->error_handler = new \RCSE\Core\Handlers\ErrorHandler();
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
    private function read_file(string $file) : string
    {
        $file_handler;
        $file_contents;
        $file_path = ROOT . $file;

        if ($this->debug) {
            $this->logger->write_to_log("Reading file: $file_path!\n", "debug");
        }

        if (is_readable($file_path) === false) {
            if ($this->debug) {
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

        if ($this->debug) {
            $this->logger->write_to_log("File read!\n", "debug");
        }

        return $file_contents;
    }

    private function get_page(string $name)
    {
        $path = ROOT . "themes/" . $this->config->get_main_config()['theme'] . "/pages/" . $name . ".html";
        
        try {
            $file = $this->read_file($path);
        } catch (\Exception $e) {
            $message = ERROR_PREFIX_JSON . "(" . $e->getCode() . ")" . $e->getMessage() . "!\n" . REPORT_ERROR . RECONFIG_REQUIRED;
            $this->error_handler->print_error_and_redirect($this->logger, "critical", $message, "admin");
            return false;
        }

        return $file;
    }

    public function set_element_data(string $element, string $data)
    {
        $this->data[$element] = $data; 
    }

    public function set_page_data(array $elements, array $data)
    {
        for($i = 0; $i < count($elements); $i++) {
            $this->data[$elements[$i]] = $data[$i];
        }
    }

    public function print_page(string $name)
    {
        $page = $this->get_page($name);

        foreach($this->data as $key => $content) {
            $key = strtoupper($key);

            $page = str_replace("{$key}", $content, $page);
        }

        print($page);
    }
}
