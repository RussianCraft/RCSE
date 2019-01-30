<?php
declare(strict_types=1);
namespace RCSE\Core\Exceptions;

class FileLockException extends \Exception
{
    private
        $file_path;

    public function __construct(string $file_path) 
    {
        parent::__construct("Failed to lock file: " . $file_path, 02);
        $this->file_path = $file_path;
    }
}