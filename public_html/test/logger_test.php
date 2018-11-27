<?php
require "../vendor/autoload.php";

$config = new Core\JSONManager();

echo "Causing JSON error to log: ";
$config->get_main_config("syte");