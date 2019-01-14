<?php
declare(strict_types=1);
namespace RCSE\Interaction;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}
if (defined("RECONFIG_REQUIRED") === false) {
    define("RECONFIG_REQUIRED", "Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n");
}
if (defined("REPORT_ERROR") === false) {
    define("REPORT_ERROR", "Check your source code or send this message (with error) to Issues at GitHub!\n");
}

class UserManager
{
    public function get_data_user(string $login, string $type)
    {
        
    }

    public function set_data_user() : bool
    {
    }

    public function auth_user() : bool
    {
    }
    
    public function register_user() : bool
    {
    }

    public function punish_user() : bool
    {
    }

    public function remove_user() : bool
    {
    }

    public function delete_user() : bool
    {
    }
}
