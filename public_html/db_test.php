<?php
require "vendor/autoload.php";

$db = new Core\DBManager();

var_dump($db->check_data("accounts", "by_login", "Test"));
echo "<br>";
var_dump($db->get_data("accounts", "by_login", "Test"));