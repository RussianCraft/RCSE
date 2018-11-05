<?php
require "vendor/autoload.php";

$config = new Core\ConfigManager();

$config->get_main_config();
$config->get_queries("users");