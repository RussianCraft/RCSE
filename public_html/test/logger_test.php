<?php
require "../vendor/autoload.php";

$config = new RCSE\Core\JSONManager();

echo "Causing JSON error to log: ";
$config->get_main_config("syte");