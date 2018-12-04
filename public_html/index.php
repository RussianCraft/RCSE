<?php

require "/vendor/autoload.php";

$template = new RCSE\Interaction\TemplateManager();

$template->set_element_data("lang", "ru");
$template->set_element_data("test", "Hello world!");

$template->print_page("test");