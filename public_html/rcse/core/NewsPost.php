<?php
declare(strict_types=1);
namespace RCSE\Core;

class NewsPost
{
    private $file;

    private $user;

    private $database;

    private $logger;

    private $config;

    private $utils;

    public function __construct(Logger $logger, Configurator $config)
    {}

    public function newsPostGetPostsList()
    {}

    public function newsPostGetPostData(string $post_id)
    {}

    public function newsPostCreatePost(string $title, string $login, string $description, string $tags, string $content)
    {}

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
    {}

    public function newsPostCreateComment(string $post_id, string $login, string $content)
    {}

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