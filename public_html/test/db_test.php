<?php
require "../vendor/autoload.php";
if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

$logger = new \RCSE\Core\Logger();
$config = new \RCSE\Core\Configurator($logger);
$db = new \RCSE\Core\Database($logger, $config);

$data = [
    ":login" => "Test1",
    ":password" => "hhfgdfgrfdfew2341",
    ":email" => "test@ya.d",
    ":sex" => "m",
    ":birthdate" => "0000-00-00",
    ":origin" => "MSK",
    ":regdate" => date("Y-m-d"),
    ":settings" => "{}"
];

echo "Checking DB for account \"Test\": ";
var_dump($db->databaseCheckData("accounts", "by_login", "Test"));
echo "<br>";
echo "Reading account \"Test\": ";
var_dump($db->databaseGetData("accounts", "by_login", "Test"));
echo "<br>";
echo "Writing account \"Test1\" to DB: ";
var_dump($db->databaseSendData("accounts", "insert", $data));
echo "<br>";
echo "Updating account \"Test1\": ";
var_dump($db->databaseSendData("accounts", "update_email", [":email" => "test@yh.d"], "Test1"));
echo "<br>";
echo "Removing account \"Test1\": ";
var_dump($db->databaseDeleteData("accounts", "Test1"));


