<?php
declare(strict_types=1);
namespace RCSE\Core;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

/** File Handler, provides functions to write and read files */
class File
{
    /** @var bool|resource Opened file pointer or bool answer if file opening fails */
    private $file_stream;

    /** @var string */
    private $file_name;

    /** @var string */
    private $file_dir;

    /** @var integer */
    private $file_perms = 0777;

    /**
     * If you intend to use fileWriteLine, you'll have to set $file_dir and $file_name here
     *
     * @param string $file_dir [optional] File directory w\o ROOT directory
     * @param string $file_name [optaional] Filename
     */
    public function __construct(string $file_dir = null, string $file_name = null)
    {
        if ($file_dir && $file_name) {
            $this->file_dir = ROOT . $file_dir;
            $this->file_name = $file_name;
        }
    }

    public function __destruct()
    {
        if ($this->file_steram) {
            $this->fileClose();
        }
    }

    /**
     * Tries to open and lock file, based on $mode.
     *
     * @param string $mode fopen mode - "c" for creating and writing, "r" for reading
     * @return void Doesn't return anything, fills the $file_stream variable of class
     * @throws \Exception In case of fopen failure
     * @throws \Exception In case of flock failure
     */
    public function fileOpen(string $mode)
    {
        $lock = "";
        
        if (is_dir($this->file_dir) === false) {
            $this->fileCreateDir();
        } else {
            $this->fileSetPermissions();
        }

        switch ($mode) {
                case "r":
                    $lock = LOCK_SH;
                    break;
                case "c":
                    $lock = LOCK_EX;
                    break;
        }
        $this->file_stream = fopen($this->file_dir . $this->file_name, $mode."b");
        if ($this->file_stream === false) {
            throw new \Exception("Failed to create file: {$this->file_name}!", 1000);
        }

        if (flock($this->file_stream, $lock, $eWouldBlock) === false) {
            throw new \Exception("Failed to lock the file: {$this->file_path}!", 1001);
        }

        rewind($this->file_stream);
    }

    /**
     * Simply creates directory
     *
     * @return void Returns nothing
     */
    private function fileCreateDir()
    {
        mkdir($this->file_dir, $this->file_perms);
    }

    /**
     * Checks, wether target directory is read-\write- able, if not - tries to chmod it
     *
     * @return void Returns nothing
     * @throws \Exception In case of chmod failure
     */
    private function fileSetPermissions()
    {
        if (is_readable($this->file_dir) === false || is_writeable($this->file_dir) === false) {
            if (chmod($this->file_dir, $this->file_perms) === false) {
                throw new \Exception("Failed to set file write-\\read- able: {$this->file_path}!", 1002);
            } elseif (is_readable($this->file_dir) === false || is_writeable($this->file_dir) === false) {
                throw new \Exception("Failed to set file write-\\read- able: {$this->file_path}!", 1002);
            }
        }
    }

    /**
     * Simply unlocks and closes file, also clears stat cache
     *
     * @return void Returns nothing
     */
    private function fileClose()
    {
        clearstatcache();
        flock($this->file_stream, LOCK_UN);
        fclose($this->file_stream);
    }

    /**
     * Tries to read target file
     *
     * @param string $file_dir File directory w\o ROOT directory
     * @param string $file_name Filename
     * @return string Contents of file
     * @throws \Exception In case of fread failure
     */
    public function fileRead(string $file_dir, string $file_name) : string
    {
        $file_contents = "";
        $this->file_dir = ROOT . $file_dir;
        $this->file_name = $file_name;
        $this->fileOpen("r");

        $file_contents = fread($this->file_stream, filesize($this->file_dir.$this->file_name));

        if ($file_contents === false) {
            throw new \Exception("Failed to read from file: {$this->file_path}!", 1003);
        }

        $this->fileClose();

        return $file_contents;
    }

    /**
     * Tries to overwrite the whole file at once
     *
     * @param string $file_dir File directory w\o ROOT directory
     * @param string $file_name Filename
     * @param string $contents Contents to write
     * @return void Returns nothing
     * @throws \Exception In case of fread failure
     */
    public function fileWrite(string $file_dir, string $file_name, string $contents)
    {
        $this->file_dir = ROOT . $file_dir;
        $this->file_name = $file_name;
        $this->fileOpen("c");

        file_put_contents($this->file_dir. $this->file_name, "");
        
        if (fwrite($this->file_stream, $contents) === false) {
            throw new \Exception("Failed to write to file: {$this->file_path}!", 1004);
        }

        $this->fileClose();
    }

    /**
     * Tries to write a single line. Requires class init with $file_dir and $file_name
     *
     * @param string $contents Content to write
     * @return void Returns nothing
     * @throws \Exception In case of fwrite failure
     */
    public function fileWriteLine(string $contents)
    {
        if (fwrite($this->file_stream, $contents) === false) {
            throw new \Exception("Failed to write line to file: {$this->file_path}!", 1005);
        }
        
        fflush($this->file_stream);
    }
}
