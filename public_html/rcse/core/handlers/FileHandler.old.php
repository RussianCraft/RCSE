<?php
declare(strict_types=1);
namespace RCSE\Core\Handlers;


class FileHandler
{
    public function __construct()
    {}

    /**
     * Reads contents of /$file(i.e. /configs/main.json), if file not present or not readable throws FileNotFoundException,
     * if present, but locked throws FileLockException
     *
     * @param string $file Filename, must end with .json (i.e. "main.json)
     * @throws \RCSE\Core\Exceptions\FileNotFoundException
     * @throws \RCSE\Core\Exception\FileLockException
     * @return string Contents of the file
     */
    public function read_file(string $file) : string
    {
        $file_handler;
        $file_contents;
        $file_path = ROOT . $file;

        if (is_readable($file_path) === false) {
            
            chmod($file_path, 0777);
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
    
        clearstatcache();
        flock($file_handler, LOCK_UN);
        fclose($file_handler);

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
    public function write_file(string $file, string $contents) : bool
    {
        $file_handler;
        $file_path = ROOT . $file;

        if (is_writeable($file_path) === false) {

            chmod($file_path, 0777);
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

        clearstatcache();
        flock($file_handler, LOCK_UN);
        fclose($file_handler);

        return true;
    }

}