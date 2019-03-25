<?php
require "../vendor/autoload.php";

$config = new RCSE\Core\Configurator();


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

$logging = ["debug" => true];

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


echo "Reading main site config: <br>";
var_dump($config->configObtainMain('site'));
echo "<br>";
echo "Writing main site config: <br>";
var_dump($config->configUpdateMain("site", $configuration));
echo "<br>";
echo "Reading modified main site config: <br>";
var_dump($config->configObtainMain('site'));
echo "<br>";
echo "Reading queries for \"accounts\": ";
var_dump($config->configObtainQueries('accounts'));
echo "<br>";
echo "Reading locale: <br>";
var_dump($config->configObtainLocale('ru', 'errors', 'RCS'));
echo "<br>";
echo "Reading \"logger\" module config: <br>";
var_dump($config->configObtainModuleProps('logmanager'));
echo "<br>";
echo "Writing \"logger\" module config: <br>";
var_dump($config->configUpdateModuleProps("logmanager", $logging));
echo "<br>";
echo "Reading modified \"logger\" module config: <br>";
var_dump($config->configObtainModuleProps('logmanager'));
echo "<br>";
echo "Reading usergroups: <br>";
var_dump($config->configObtainUsergroup("all"));
echo "<br>";
echo "Writing new usergroup: <br>";
var_dump($config->configUpdateUsergroup("user1", $usergroup));
echo "<br>";
echo "Reading modified usergroups: <br>";
var_dump($config->configObtainUsergroup("user1"));
echo "<br>";
echo "Removing usergroup \"user1\": <br>";
var_dump($config->configRemoveUsergroup("user1"));
echo "<br>";
echo "Reading modified usergroups: <br>";
var_dump($config->configObtainUsergroup("user1"));