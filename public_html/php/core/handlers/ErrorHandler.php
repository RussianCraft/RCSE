<?php
declare(strict_types=1);
namespace Core\Handlers;

class ErrorHandler
{
    /**
     * Undocumented function
     *
     * @param Core\LogManager $logger
     * @param string $message
     * @param string $redirect
     * @return void
     */
    public function print_error_and_redirect(LogManager $logger, string $message, string $redirect="home") 
    {
        
        switch($redirect) {

        }
        $message = str_replace("\n", '<br>', $message);
        print($message);
    }
}