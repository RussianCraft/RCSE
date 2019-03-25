<?php
require("../vendor/autoload.php");

define("ROOT", $_SERVER['DOCUMENT_ROOT']);

$logger = new \RCSE\Core\Logger();

$logger->log($logger::DEBUG, "Debug");
$logger->log($logger::INFO, "System online");

//$logger = new \RCSE\Core\LogManager("test.log");


