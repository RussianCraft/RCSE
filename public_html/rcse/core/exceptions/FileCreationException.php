<?php
declare(strict_types=1);
namespace RCSE\Core\Exceptions;

class FileCreationException extends \Exception
{
    private
        $file_path;

    public function __construct(string $file_path) 
    {
        parent::__construct("Failed to create file: " . $file_path, 04);
        $this->file_path = $file_path;
    }
}