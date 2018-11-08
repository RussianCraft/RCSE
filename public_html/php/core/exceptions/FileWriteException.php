<?php
declare(strict_types=1);
namespace Core\Exceptions;

class FileWriteException extends \Exception
{
    private
        $file_path;

    public function __construct(string $file_path) 
    {
        \Exception::__construct("Failed to write to file: " . $file_path, 03);
        $this->file_path = $file_path;
    }
}