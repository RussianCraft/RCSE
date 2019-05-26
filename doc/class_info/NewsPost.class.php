<?php

NewsPost {
    public __construct(Logger $logger, Configurator $config)
    public newsPostGetPostsList()
    public newsPostGetPostData(string $post_id)
    public newsPostCreatePost(string $title, string $login, string $description, string $tags, string $content)
    public newsPostEditPost(string $title, string $description, string $tags, string $content)
    public newsPostVoteupPost(string $post_id)
    public newsPostVotedownPost(string $post_id)
    public newsPostHidePost(string $post_id)
    public newsPostRemovePost(string $post_id)
    public newsPostGetCommentList(string $post_id)
    public newsPostCreateComment(string $post_id, string $login, string $content)
    public newsPostEditComment(string $comment_id, string $content)
    public newsPostVoteupComment(string $comment_id)
    public newsPostVotedownComment(string $comment_id)
    public newsPostHideComment(string $comment_id)
    public newsPostRemoveComment(string $comment_id)
}