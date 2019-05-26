<?php

Forum {
    public __construct(Logger $logger, Configurator $config)
    public forumGetSectionsList()
    public forumCreateSection(string $section, array $settings)
    public forumEditSection(string $section, array $settings)
    public forumRemoveSection(string $section)
    public forumGetTopicsList(string $section)
    public forumGetTopicData(string $topic_id)
    public forumCreateTopic(string $title, string $login, string $section, string $tags, string $content)
    public forumEditTopic(string $title, string $description, string $tags, string $content)
    public forumVoteupTopic(string $topic_id)
    public forumVotedownTopic(string $topic_id)
    public forumHideTopic(string $topic_id)
    public forumRemoveTopic(string $topic_id)
    public forumGetReplyList(string $topic_id)
    public forumCreateReply(string $topic_id, string $login, string $content)
    public forumEditReply(string $reply_id, string $content)
    public forumVoteupReply(string $reply_id)
    public forumVotedownReply(string $reply_id)
    public forumHideReply(string $reply_id)
    public forumRemoveReply(string $reply_id)
}