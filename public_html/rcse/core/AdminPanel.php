<?php
declare(strict_types=1);
namespace RCSE\Core;

class AdminPanel
{
    private $newsPost;

    private $forum;

    private $config;

    private $logger;

    private $user;

    public function __construct(Logger $logger, Configurator $configurator)
    {}

    private function adminPanelGetMainSettings()
    {}

    public function adminPanelCreateMainSettingsPane()
    {}

    public function adminPanelCreatePostsListPane()
    {}

    public function adminPanelCreateForumSectionsListPane()
    {}

    public function adminPanelCreateForumPostsListPane(string $section)
    {}

    public function adminPanelCreateUsersList()
    {}
}