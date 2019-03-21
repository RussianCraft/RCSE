<?php
require("../vendor/autoload.php");

define("ROOT", "/");

$logger = new \RCSE\Core\Logger();

$logger->log($logger::INFO, "System online");
