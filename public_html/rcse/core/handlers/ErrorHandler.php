<?php
declare(strict_types=1);
namespace RCSE\Core\Handlers;

class ErrorHandler
{

    /**
     * Undocumented function
     *
     * @param \Core\LogManager $logger
     * @param string $severity
     * @param string $message
     * @param string $redirect
     * @return void
     */
    public function print_error_and_redirect(\RCSE\Core\LogManager $logger, string $severity, string $message, string $redirect="home") : void
    {
        switch ($redirect) {
            case "home":
                $destination = "/";
                break;
            case "admin":
                $destination = "/admin.php ";
                break;
            case "user":
                $destination = "/user.php";
                break;
            case "forum":
                $destination = "/forum.php";
                break;
            case "news":
                $destination = "/news.php";
                break;
            case "prev":
                if(empty($_SERVER['HTTP_REFERER']) === false) {
                    $destination = $_SERVER['HTTP_REFERER'];
                } else {
                    $destination = "/";
                }
                break;
        }

        $logger->write_to_log($message, $severity);

        header("Refresh: 5; URL=" . $destination);

        $message = str_replace("\n", '<br>', $message);
        print($message);
    }

    public function print_error(\Core\LogManager $logger, string $severity, string $message) : void
    {
        $logger->write_to_log($message, $severity);

        $message = str_replace("\n", '<br>', $message);
        print($message);
    }

    public function print_error_no_log(string $message) : void
    {
        $message = str_replace("\n", '<br>', $message);
        print($message);
    }
}
