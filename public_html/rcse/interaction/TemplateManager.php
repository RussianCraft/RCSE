<?php
declare(strict_types=1);
namespace RCSE\Interaction;

class TemplateManager 
{
private $data = [];
private $template;
private $logger;
private $config;

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

    public function get_page()
    {

    }

    public function set_data() 
    {
        
    }

    public function print_page()
    {

    }
}