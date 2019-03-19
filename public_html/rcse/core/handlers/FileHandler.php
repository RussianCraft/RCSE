<?php
declare(strict_types=1);
namespace RCSE\Core\Handlers;

if(defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

class FileHandler
{
    private $file_stream;
    private $file_path;
    private $file_dir;
    private $file_perms = 0777;
    private $line_count = 0;
    private $flush_freq = false;

    public function __construct(string $file_dir = null)
    {
        $this->file_dir = $file_dir;
    }

    public function __destruct()
    {
        if($this->file_steram !== null) {
            $this->fileClose();
        }
    }

    private function fileOpen(string $mode)
    {
        $lock;

        if(file_exists($this->file_path) === false) {
            if(file_exists($this->file_dir) === false) {
                $this->fileCreateDir();
            }

        } else {
            $this-fileSetPermissions();

            switch($mode) {
                case "r":
                    $lock = LOCK_SH;
                    break;
                case "w":
                    $lock = LOCK_EX;
                    break;
            }
        }
        
        $this->file_stream = fopen($this->file_path, $mode."b");

        if(flock($this->file_stream, $lock, $eWouldBlock) === false) {
            throw new \Exception("Failed to lock the file: {$this->file_path}!", 1000);
        }
    }

    private function fileCreateDir()
    {
        mkdir($this->file_dir, $this->file_perms, true);
    }

    private function fileSetPermissions()
    {
        if(is_readable($this->file_path) === false || is_writeable($file_path) === false) {

            if(chmod($this->file_path, $this->file_perms) === false) {
                throw new \Exception("Failed to set file write-\\read- able: {$this->file_path}!", 1001);
            }
        }
    }

    private function fileClose()
    {
        clearstatcache();
        flock($this->file_stream, LOCK_UN);
        fclose($this->file_stream);
    }

    public function fileRead(string $file_path)
    {
        $file_contents;

        $this->file_path = $file_path;

        $this->fileOpen("r");

        $file_contents = file_get_contents($this->file_stream);

        if($file_contents === false) {
            throw new \Exception("Failed to read from file: {$this->file_path}!", 1002);
        }

        $this->fileClose();

        return $file_contents;
    }

    public function fileWrite(string $file_path, string $contents)
    {
        $this->file_path = $file_path;
        $this->fileOpen("w");

        if(fwrite($this->file_stream, $contents) === false) {
            throw new \Exception("Failed to write to file: {$this->file_path}!", 1003);
        }

        $this->fileClose();
    }

    public function fileWriteLine(string $file_path, string $contents)
    {
        $this->file_path = $file_path;
        $this->fileOpen("w");

        if(fwrite($this->file_stream, $contents) === false) {
            throw new \Exception("Failed to write to file: {$this->file_path}!", 1003);
        } else {
            $this->line_count++;

            if($this->flush_freq && $this->line_count % $this->flush_freq === 0) {
                fflush($this->file_stream);
            }
        }
    }
}