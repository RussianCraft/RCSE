<?php
declare(strict_types=1);
use RCSE\Core\Logger;
use RCSE\Core\Configurator;
use RCSE\Core\Utils;
use RCSE\Core\Database;
use RCSE\Core\File;

namespace RCSE\Core;

class Forum
{
    private $file;

    private $database;

    private $logger;

    private $config;

    private $utils;

    public function __construct(Logger $logger, Configurator $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->utils = new Utils();
        $this->file = new File();
        $this->database = new Database($this->logger, $this->config);
    }

    public function forumGetSectionsList()
    {
        return $this->config->configObtainSection();
    }

    public function forumCreateSection(string $section, array $settings)
    {}

    public function forumEditSection(string $section, array $settings)
    {}

    public function forumRemoveSection(string $section)
    {}

    public function forumGetTopicsList(string $section)
    {
        try {
            return $this->database->databaseGetData('topics', 'by_section', $section);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return $e->getMessage();
        }
    }

    public function forumGetTopicsListAll()
    {
        try {
            return $this->database->databaseGetData('topics', 'all');
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return $e->getMessage();
        }
    }

    public function forumGetTopicData(string $topic_id)
    {
        if($this->database->databaseCheckData('topics', 'by_topic_id', $topic_id) === false) {
            $message = "Post for {$topic_id} not found!";
            $this->logger->log($this->logger::ERROR, $message, get_class($this));
            return $message;
        }

        try {
            return $this->database->databaseGetData('topics', 'by_topic_id', $topic_id);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return $e->getMessage();
        }
    }

    public function forumCreateTopic(string $title, string $login, string $section, string $tags, string $content)
    {
        $date_unf = new \DateTime();
        $date = $date_unf->format('Y-m-d');

        $settings = [];

        $params = [
            ":title" => $title,
            ":date" => $date,
            ":author" => $login,
            ":section" => $section,
            ":tags" => $tags,
            ":contents" => $content,
            ":settings" => json_encode($settings)
        ];

        try {
            return $this->database->databaseSendData('topics', 'insert', $params);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return false;

        }
    }

    public function forumEditTopic(string $title, string $description, string $tags, string $content)
    {}

    public function forumVoteupTopic(string $topic_id)
    {}

    public function forumVotedownTopic(string $topic_id)
    {}

    public function forumHideTopic(string $topic_id)
    {}

    public function forumRemoveTopic(string $topic_id)
    {}

    public function forumGetReplyList(string $topic_id)
    {
        if ($this->database->databaseCheckData('replies', 'by_reply_to', $topic_id) === false) {
            return "No comments here";
        }

        try {
            return $this->database->databaseGetData('replies', 'by_reply_to', $topic_id);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return $e->getMessage();
        }}

    public function forumCreateReply(string $topic_id, string $login, string $content)
    {
        $date_unf = new \DateTime();
        $date = $date_unf->format('Y-m-d');

        $settings = [];

        $params = [
            ":reply_to" => $topic_id,
            ":date" => $date,
            ":author" => $login,
            ":content" => $content,
            ":settings" => json_encode($settings)
        ];

        try {
            return $this->database->databaseSendData('replies', 'insert', $params);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return false;

        }
    }

    public function forumEditReply(string $reply_id, string $content) 
    {}

    public function forumVoteupReply(string $reply_id)
    {}

    public function forumVotedownReply(string $reply_id)
    {}

    public function forumHideReply(string $reply_id)
    {}

    public function forumRemoveReply(string $reply_id)
    {}
}