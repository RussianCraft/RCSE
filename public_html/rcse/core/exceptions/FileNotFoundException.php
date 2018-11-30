<?php
declare(strict_types=1);
namespace RCSE\Core\Exceptions;

class FileNotFoundException extends \Exception
{
    private
        $file_path;

    public function __construct(string $file_path) 
    {
        \Exception::__construct("File not found (or not read-\write- able): " . $file_path, 01);
        $this->file_path = $file_path;
    }
}