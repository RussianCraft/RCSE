<?php
declare(strict_types=1);
namespace RCSE\Core\Handlers;


class FileHandler
{
    private $logger;
    private $error_handler;
    private $debug;

    public function __construct()
    {
        //$this->logger = new \RCSE\Core\LogManager(get_class($this), $this);
        //$this->error_handler = new ErrorHandler();
    }

    /**
     * Reads contents of /$file(i.e. /configs/main.json), if file not present or not readable throws FileNotFoundException,
     * if present, but locked throws FileLockException
     *
     * @param string $file Filename, must end with .json (i.e. "main.json)
     * @throws \RCSE\Core\Exceptions\FileNotFoundException
     * @throws \RCSE\Core\Exception\FileLockException
     * @return string Contents of the file
     */
    public function read_file(string $file, bool $log = true, bool $debug = false) : string
    {
        $file_handler;
        $file_contents;
        $file_path = ROOT . $file;

        if ($log && $debug) {
            $this->logger->write_to_log("Reading file: $file_path!\n", "debug");
        }

        if (is_readable($file_path) === false) {
            if ($log) {
                $this->logger->write_to_log("FIle is not readable! Trying chmod(0766)!\n", "notice");
            }
            chmod($file_path, 0766);
            if (is_readable($file_path) === false) {
                throw new \RCSE\Core\Exceptions\FileNotFoundException($file_path);
            }
        }

        $file_handler = fopen($file_path, "rb");

        if (flock($file_handler, LOCK_SH, $eWouldBlock) === false || $eWouldBlock) {
            fclose($file_handler);
            throw new \RCSE\Core\Exceptions\FileLockException($file_path);
        }
        
        $file_contents = fread($file_handler, filesize($file_path));
    
        flock($file_handler, LOCK_UN);
        fclose($file_handler);

        if ($log && $debug) {
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
     * @throws \RCSE\Core\Exceptions\FileNotFoundException
     * @throws \RCSE\Core\Exceptions\FileWriteException
     * @return boolean True in case of success
     */
    public function write_file(string $file, string $contents, bool $debug = false) : bool
    {
        $file_handler;
        $file_path = ROOT . $file;

        if ($debug) {
            $this->logger->write_to_log("Writing to file: $file_path!\n", "debug");
        }

        if (is_writeable($file_path) === false) {
            if ($debug) {
                $this->logger->write_to_log("FIle is not writeable! Trying chmod(0766)!\n", "notice");
            }

            chmod($file_path, 0766);
            if (is_writeable($file_path) === false) {
                throw new \RCSE\Core\Exceptions\FileNotFoundException($file_path);
            }
        }

        $file_handler = fopen($file_path, "wb");

        while (flock($file_handler, LOCK_EX, $eWouldBlock) === false) {
        }

        if (fwrite($file_handler, $contents) === false) {
            fclose($file_handler);
            throw new \RCSE\Core\Exceptions\FileWriteException($file_path);
        }

        flock($file_handler, LOCK_UN);
        fclose($log_handler);

        if ($debug) {
            $this->logger->write_to_log("File written!\n", "debug");
        }

        return true;
    }

}