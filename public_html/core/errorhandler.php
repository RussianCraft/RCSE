<?php
declare(strict_types=1);
namespace RCSE\Core;

class ErrorHandler
{
    /**
     * Undocumented function
     *
     * @param string $message
     * @return void
     */
    public function config_error(string $message) 
    {
        header("Refresh: 5; url=/admin.php?configerror");
        $message = str_replace("\n", '<br>', $message);
        print($message);
    }
}