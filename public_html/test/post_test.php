<?php
require "../vendor/autoload.php";

use RCSE\Core\Configurator;
use RCSE\Core\Logger;
use RCSE\Core\NewsPost;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

$logger = new Logger();
$config = new Configurator($logger);
$post = new NewsPost($logger, $config);

$create_res = $post->newsPostCreatePost("Test", "test", "<h3>Test</h3>", "test;message;", "<h3>Test message, don't mug me</h3>");
$post_list = $post->newsPostGetPostsList();
$post_data = $post->newsPostGetPostData($post_list[0]['post_id']);
$comm_res = $post->newsPostCreateComment($post_list[0]['post_id'], "test", "Hello");
$comm_list = $post->newsPostGetCommentList($post_list[0]['post_id']);

echo "Creating new post, result: ";
var_dump($create_res);
echo "<br>";
echo "<br>Reading post list: ";
var_dump($post_list);
echo "<br>";
echo "<br>Reading post data: ";
var_dump($post_data);
echo "<br>";
echo "<br>Creating comment: ";
var_dump($comm_res);
echo "<br>";
echo "<br>Reading comment list: ";
var_dump($comm_list);