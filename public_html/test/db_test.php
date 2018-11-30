<?php
require "../vendor/autoload.php";

$db = new RCSE\Core\DBManager();

$data = [
    "login" => "Test1",
    "password" => "hhfgdfgrfdfew2341",
    "email" => "test@ya.d",
    "sex" => "m",
    "brithdate" => "0000-00-00",
    "origin" => "MSK",
    "regdate" => date("Y-m-d"),
    "settings" => "{}"
];

echo "Checking DB for account \"Test\": ";
var_dump($db->check_data_db("accounts", "by_login", "Test"));
echo "<br>";
echo "Reading account \"Test\": ";
var_dump($db->get_data_db("accounts", "by_login", "Test"));
echo "<br>";
echo "Writing account \"Test1\" to DB: ";
var_dump($db->send_data_db("accounts", "insert", $data));
echo "<br>";
echo "Updating account \"Test1\": ";
var_dump($db->send_data_db("accounts", "update_email", array("email" => "test@yh.d"), "Test1"));
echo "<br>";
echo "Removing account \"Test1\": ";
var_dump($db->delete_data_db("accounts", "Test1"));


