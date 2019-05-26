<?php
require "../vendor/autoload.php";

use RCSE\Core\Configurator;
use RCSE\Core\Logger;
use RCSE\Core\Forum;

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

$logger = new Logger();
$config = new Configurator($logger);
$forum = new Forum($logger, $config);

$section_list = $forum->forumGetSectionsList();
$topic_res = $forum->forumCreateTopic("Test", "test", "default", "test;topic", "<h3>Message</h3>");
$topics = $forum->forumGetTopicsList("default");
$topic_data = $forum->forumGetTopicData($topics[0]['topic_id']);
$reply_res = $forum->forumCreateReply($topics[0]['topic_id'], "test", "Reply");
$reply = $forum->forumGetReplyList($topics[0]['topic_id']);


echo "Creating new topic, result: ";
var_dump($topic_res);
echo "<br>";
echo "<br>Reading topic list: ";
var_dump($topics);
echo "<br>";
echo "<br>Reading topic data: ";
var_dump($topic_data);
echo "<br>";
echo "<br>Creating reply: ";
var_dump($reply_res);
echo "<br>";
echo "<br>Reading reply list: ";
var_dump($reply);