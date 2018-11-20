<?php
require "vendor/autoload.php";

$config = new Core\JSONManager();

/*
$usergroup = [
    "level" => 1,
    "rate" => true,
    "news" => [
        "add_new" => false,
        "edit_own" => false,
        "edit_all" => false,
        "rem_own" => false,
        "rem_others" => false
    ],
    "comments" => [
        "add_new" => true,
        "edit_own" => true,
        "edit_all" => false,
        "rem_own" => true,
        "rem_others" => false
    ],
    "accounts" => [
        "edit_own" => true,
        "edit_others" => false,
        "banhammer" => false,
        "rem_own" => true,
        "rem_others" => false,
        "full_view" => false
    ],
    "admin" => false
];

$logging = ["debug" => false];

$configuration = [
    "name" => "RCSE",
    "about" => "RCSE Test",
    "keywords" => "hello, test, aperture",
    "installed" => false,
    "offline" => false,
    "theme" => "RCS",
    "lang" => "ru",
    "start_page" => "1",
    "plugins" => false,
    "log" => false
];
*/

var_dump($config->get_main_config());
echo "<br>";
/*var_dump($config->set_main_config("site", $config));
echo "<br>";
var_dump($config->get_main_config("site"));
echo "<br>";*/
var_dump($config->get_queries("users"));
echo "<br>";
var_dump($config->get_locale("cms", "common", "en", "errors"));
echo "<br>";
var_dump($config->get_modules_properties("logmanager"));
echo "<br>";
var_dump($config->get_usergroups());
echo "<br>";
/*var_dump($config->set_modules("logmanager", $logging));
echo "<br>";
var_dump($config->get_modules_properties("logmanager"));
echo "<br>";
var_dump($config->set_usergroups("user1", $usergroup));
echo "<br>";
var_dump($config->get_usergroups("user1"));
echo "<br>";
var_dump($config->remove_usergroup("user1"));
echo "<br>";
var_dump($config->get_usergroups("user1"));*/
