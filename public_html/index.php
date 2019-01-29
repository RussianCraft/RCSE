<?php

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

require ROOT . "/vendor/autoload.php";

$template = new RCSE\Interaction\TemplateManager();
$config = new RCSE\Core\JSONManager();
$db = new RCSE\Core\DBManager();

$theme = $config->get_data_json('main',['entry' => 'site'])['theme'];

$template->set_element_data("lang", "ru");
$template->set_element_data("style", "http://rcse/resources/themes/" . $theme . "/pages/structure/structure.css");

$template->print_page("structure");