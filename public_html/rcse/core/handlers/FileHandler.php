<?php
declare(strict_types=1);
namespace RCSE\Core\Handlers;

if(defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

class FileHandler
{
    private $file_stream;
    private $file;
    private $file_dir;
    private $file_perms = 0777;
    private $line_count = 0;
    private $flush_freq = false;

    public function __destruct()
    {
        if($this->file_steram !== null) {
            $this->fileClose();
        }
    }

    private function fileOpen(string $mode)
    {
        $lock;

        var_dump($this->file_dir);
        
        if(is_dir($this->file_dir) === false) {
            $this->fileCreateDir();
        } else {
            $this->fileSetPermissions();

            switch($mode) {
                case "r":
                    $lock = LOCK_SH;
                    break;
                case "c":
                    $lock = LOCK_EX;
                    break;
            }
        }
        $this->file_stream = fopen($this->file_dir . $this->file, $mode."b");
        if($this->file_stream === false) {
            throw new \Exception("Failed to create file: {$this->file}!", 1000);
        }

        if(flock($this->file_stream, $lock, $eWouldBlock) === false) {
            throw new \Exception("Failed to lock the file: {$this->file_path}!", 1001);
        }
    }

    private function fileCreateDir()
    {
        mkdir($this->file_dir, $this->file_perms, true);
    }

    private function fileSetPermissions()
    {
        if(is_readable($this->file_dir) === false || is_writeable($this->file_dir) === false) {

            if(chmod($this->file_dir, 0777) === false) {
                throw new \Exception("Failed to set file write-\\read- able: {$this->file_path}!", 1002);
            } elseif(is_readable($this->file_dir) === false || is_writeable($this->file_dir) === false) {
                throw new \Exception("Failed to set file write-\\read- able: {$this->file_path}!", 1002);
            }
        }
    }

    private function fileClose()
    {
        clearstatcache();
        flock($this->file_stream, LOCK_UN);
        fclose($this->file_stream);
    }

    public function fileRead(string $file_dir, string $file)
    {
        $file_contents;
        $this->file_dir = $file_dir;
        $this->file = $file;
        $this->fileOpen("r");

        $file_contents = file_get_contents($this->file_stream);

        if($file_contents === false) {
            throw new \Exception("Failed to read from file: {$this->file_path}!", 1003);
        }

        $this->fileClose();

        return $file_contents;
    }

    public function fileWrite(string $file_dir, string $file, string $contents)
    {
        $this->file_dir = $file_dir;
        $this->file = $file;
        $this->fileOpen("c");

        if(fwrite($this->file_stream, $contents) === false) {
            throw new \Exception("Failed to write to file: {$this->file_path}!", 1004);
        }

        $this->fileClose();
    }

    public function fileWriteLine(string $file_dir, string $file, string $contents)
    {
        $this->file_dir = $file_dir;
        $this->file = $file;
        $this->fileOpen("c");

        if(fwrite($this->file_stream, $contents) === false) {
            throw new \Exception("Failed to write to file: {$this->file_path}!", 1005);
        } else {
            $this->line_count++;

            if($this->flush_freq && $this->line_count % $this->flush_freq === 0) {
                fflush($this->file_stream);
            }
        }
    }
}