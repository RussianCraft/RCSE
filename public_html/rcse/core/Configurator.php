<?php
declare(strict_types=1);
namespace RCSE\Core;

class Configurator extends JSONParser
{

    private $config_path = [
        "main" => "/config/main.json",
        "modules" => "/config/modules.json",
        "queries" => "/config/queries.json",
        "forum_sections" => "/config/forum_sections.json",
        "ban_types" => "/config/ban_types.json",
        "forbidden_words" => "/config/forbidden_words.json",
        "usergroups" => "/config/usergroups.json",
        "menu" => "/config/menu.json"
    ];

    public function configObtainMain(string $type)
    {
        $types = ["site", "database"];

        return $this->jsonObtainDataSimple($type, $this->config_path["main"], $types);
    }

    public function configUpdateMain(string $type, array $contents) : bool
    {
        $types = ["site", "database"];

        return $this->jsonUpdateDataSimple($type, $this->config_path["main"], $types, $contents);
    }

    public function configObtainQueries(string $table)
    {
        $types = ["accounts", "punishments", "posts", "comments", "topics", "replies"];

        return $this->jsonObtainDataSimple($table, $this->config_path["queries"], $types);
    }

    public function configObtainModuleProps(string $module)
    {
        $types = ["dbmanager", "logmanager", "thememanager", "newsletter", "users", "forum", "search", "adminpanel", "papi"];

        return $this->jsonObtainDataSimple($module, $this->config_path["modules"], $types);
    }

    public function configUpdateModuleProps(string $module, array $contents) : bool
    {
        $types = ["dbmanager", "logmanager", "thememanager", "newsletter", "users", "forum", "search", "adminpanel", "papi"];
        
        return $this->jsonUpdateDataSimple($module, $this->config_path["modules"], $types, $contents);
    }

    public function configObtainLocale(string $lang, string $element, string $source)
    {
        $path = "/resources/locale/". $source ."/lang.json";
        $types = ["errors", "info", "panel", "user"];

        return $this->jsonObtainDataDouble($lang, $element, $path, $types);
    }

    public function configObtainUsergroup(string $group)
    {
        return $this->jsonObtainDataAllNSmall($group, $this->config_path["usergroups"], []);
    }

    public function configUpdateUsergroup(string $group, array $contents)
    {
        return $this->jsonUpdateDataSimple($group, $this->config_path["usergroups"], [], $contents);
    }

    public function configRemoveUsergroup(string $group)
    {
        return $this->jsonRemoveDataSimple($group, $this->config_path["usergroups"]);
    }

    public function configObtainWords(string $type)
    {
        $types = ["login", "swears"];

        return $this->jsonObtainDataAllNSmall($type, $this->config_path["forbidden_words"], $types);
    }

    public function configUpdateWords(string $type, array $contents)
    {
        $types = ["login", "swears"];

        return $this->jsonUpdateDataSimple($type, $this->config_path["forbidden_words"], $types, $contents);
    }

    public function configObtainSection(string $type)
    {
        return $this->jsonObtainDataAllNSmall($type, $this->config_path["forum_sections"], []);
    }

    public function configUpdateSection(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["forum_sections"], [], $contents);
    }

    public function configRemoveSection(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["forum_sections"]);
    }

    public function configObtainBan(string $type)
    {
        return $this->jsonObtainDataAllNSmall($type, $this->config_path["ban_types"], []);
    }

    public function configUpdateBan(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["ban_types"], [], $contents);
    }

    public function configRemoveBan(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["ban_types"]);
    }
    
    public function configObtainMenu(string $type)
    {
        return $this->jsonObtainDataAllNSmall($type, $this->config_path["menu"], []);
    }

    public function configUpdateMenu(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["menu"], [], $contents);
    }

    public function configRemoveFromMenu(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["menu"]);
    }
}