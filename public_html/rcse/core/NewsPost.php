<?php
declare(strict_types=1);

use RCSE\Core\Logger;
use RCSE\Core\Configurator;
use RCSE\Core\Utils;
use RCSE\Core\Database;

namespace RCSE\Core;

class NewsPost
{
    private $database;

    private $logger;

    private $config;

    private $utils;

    public function __construct(Logger $logger, Configurator $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->utils = new Utils();
        $this->database = new Database($this->logger, $this->config);
    }

    public function newsPostGetPostsList()
    {
        try {
            return $this->database->databaseGetData('posts', 'all');
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return $e->getMessage();
        }
    }

    public function newsPostGetPostData(string $post_id)
    {
        if($this->database->databaseCheckData('posts', 'by_post_id', $post_id) === false) {
            $message = "Post for {$post_id} not found!";
            $this->logger->log($this->logger::ERROR, $message, get_class($this));
            return $message;
        }

        try {
            return $this->database->databaseGetData('posts', 'by_post_id', $post_id);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return $e->getMessage();
        }
    }

    public function newsPostCreatePost(string $title, string $login, string $description, string $tags, string $content)
    {
        $date_unf = new \DateTime();
        $date = $date_unf->format('Y-m-d');

        $settings = [];

        $params = [
            ":title" => $title,
            ":date" => $date,
            ":author" => $login,
            ":description" => $description,
            ":tags" => $tags,
            ":content" => $content,
            ":settings" => json_encode($settings)
        ];

        try {
            return $this->database->databaseSendData('posts', 'insert', $params);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return false;

        }
    }

    public function newsPostEditPost(string $title, string $description, string $tags, string $content)
    {}

    public function newsPostVoteupPost(string $post_id)
    {}

    public function newsPostVotedownPost(string $post_id)
    {}

    public function newsPostHidePost(string $post_id)
    {}

    public function newsPostRemovePost(string $post_id)
    {}

    public function newsPostGetCommentList(string $post_id)
    {
        if ($this->database->databaseCheckData('comments', 'by_reply_to', $post_id) === false) {
            return "No comments here";
        }

        try {
            return $this->database->databaseGetData('comments', 'by_reply_to', $post_id);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return $e->getMessage();
        }
    }

    public function newsPostCreateComment(string $post_id, string $login, string $content)
    {
        $date_unf = new \DateTime();
        $date = $date_unf->format('Y-m-d');

        $settings = [];

        $params = [
            ":reply_to" => $post_id,
            ":date" => $date,
            ":author" => $login,
            ":contents" => $content,
            ":settings" => json_encode($settings)
        ];

        try {
            return $this->database->databaseSendData('comments', 'insert', $params);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return false;

        }
    }

    public function newsPostEditComment(string $comment_id, string $content) 
    {}

    public function newsPostVoteupComment(string $comment_id)
    {}

    public function newsPostVotedownComment(string $comment_id)
    {}

    public function newsPostHideComment(string $comment_id)
    {}

    public function newsPostRemoveComment(string $comment_id)
    {}
}