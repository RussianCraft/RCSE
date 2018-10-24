<?php
require "vendor/autoload.php";

$config = new Core\Configurator();

var_dump($config->get_main_config());