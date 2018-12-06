<?php

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

require ROOT . "/vendor/autoload.php";

$template = new RCSE\Interaction\TemplateManager();
$config = new RCSE\Core\JSONManager();
$db = new RCSE\Core\DBManager();

$theme = $config->get_main_config()['theme'];

$template->set_element_data("lang", "ru");
$template->set_element_data("style", "http://rcse/themes/" . $theme . "/pages/main.css");

if(isset($_GET['post']) === true) {
    $post_id = $_GET['post'];
    $data = $db->get_data_db("posts", "by_post_id", $_GET['post']);

    $template->set_element_data("title", $data['title']);
    $template->set_element_data("content", $data['content']);

    $template->print_page("post");
} else {
    $template->print_page("index");
}