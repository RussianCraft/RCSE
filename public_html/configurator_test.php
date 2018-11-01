<?php
require "vendor/autoload.php";

$config = new Core\Configurator();

$config->get_main_config();
$config->get_queries("users");