<?php
declare(strict_types=1);
namespace RCSE\Core;

class Configurator extends JSONParser
{

    private $config_path = [
        "main" => ["dir" => "/config/", "name" => "main.json"],
        "modules" => ["dir" => "/config/", "name" => "modules.json"],
        "queries" => ["dir" => "/config/", "name" => "queries.json"],
        "forum_sections" => ["dir" => "/config/", "name" => "forum_sections.json"],
        "ban_types" => ["dir" => "/config/", "name" => "ban_types.json"],
        "forbidden_words" => ["dir" => "/config/", "name" => "forbidden_words.json"],
        "usergroups" => ["dir" => "/config/", "name" => "usergroups.json"],
        "menu" => ["dir" => "/config/", "name" => "menu.json"]
    ];

    public function configObtainMain(string $type)
    {
        $types = ["site", "database"];

        return $this->jsonObtainDataSimple($type, $this->config_path["main"]["dir"], $this->config_path["main"]["name"], $types);
    }

    public function configUpdateMain(string $type, array $contents) : bool
    {
        $types = ["site", "database"];

        return $this->jsonUpdateDataSimple($type, $this->config_path["main"]["dir"], $this->config_path["main"]["name"], $types, $contents);
    }

    public function configObtainQueries(string $table)
    {
        $types = ["accounts", "punishments", "posts", "comments", "topics", "replies"];

        return $this->jsonObtainDataSimple($table, $this->config_path["queries"]["dir"], $this->config_path["queries"]["name"], $types);
    }

    public function configObtainModuleProps(string $module)
    {
        $types = ["dbmanager", "logmanager", "thememanager", "newsletter", "users", "forum", "search", "adminpanel", "papi"];

        return $this->jsonObtainDataSimple($module, $this->config_path["modules"]["dir"], $this->config_path["modules"]["name"], $types);
    }

    public function configUpdateModuleProps(string $module, array $contents) : bool
    {
        $types = ["dbmanager", "logmanager", "thememanager", "newsletter", "users", "forum", "search", "adminpanel", "papi"];
        
        return $this->jsonUpdateDataSimple($module, $this->config_path["modules"]["dir"], $this->config_path["modules"]["name"], $types, $contents);
    }

    public function configObtainLocale(string $lang, string $element, string $source)
    {
        $dir = "/resources/locale/". $source ."/";
        $name = "lang.json";
        $types = ["errors", "info", "panel", "user", "interface"];

        return $this->jsonObtainDataDouble($lang, $element, $dir, $name, $types);
    }

    public function configObtainUsergroup(string $group)
    {
        return $this->jsonObtainDataAllNSmall($group, $this->config_path["usergroups"]["dir"], $this->config_path["usergroups"]["name"], []);
    }

    public function configUpdateUsergroup(string $group, array $contents)
    {
        return $this->jsonUpdateDataSimple($group, $this->config_path["usergroups"]["dir"], $this->config_path["usergroups"]["name"], [], $contents);
    }

    public function configRemoveUsergroup(string $group)
    {
        return $this->jsonRemoveDataSimple($group, $this->config_path["usergroups"]["dir"], $this->config_path["usergroups"]["name"]);
    }

    public function configObtainWords(string $type)
    {
        $types = ["login", "swears"];

        return $this->jsonObtainDataAllNSmall($type, $this->config_path["forbidden_words"]["dir"], $this->config_path["forbidden_words"]["name"], $types);
    }

    public function configUpdateWords(string $type, array $contents)
    {
        $types = ["login", "swears"];

        return $this->jsonUpdateDataSimple($type, $this->config_path["forbidden_words"]["dir"], $this->config_path["forbidden_words"]["name"], $types, $contents);
    }

    public function configObtainSection()
    {
        return $this->jsonObtainDataSimpliest($this->config_path["forum_sections"]["dir"], $this->config_path["forum_sections"]["name"]);
    }

    public function configUpdateSection(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["forum_sections"]["dir"], $this->config_path["forum_sections"]["name"], [], $contents);
    }

    public function configRemoveSection(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["forum_sections"]["dir"], $this->config_path["forum_sections"]["name"]);
    }

    public function configObtainBan(string $type)
    {
        return $this->jsonObtainDataAllNSmall($type, $this->config_path["ban_types"]["dir"], $this->config_path["ban_types"]["name"], []);
    }

    public function configUpdateBan(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["ban_types"]["dir"], $this->config_path["ban_types"]["name"], [], $contents);
    }

    public function configRemoveBan(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["ban_types"]["dir"], $this->config_path["ban_types"]["name"]);
    }
    
    public function configObtainMenu()
    {
        return $this->jsonObtainDataSimpliest($this->config_path["menu"]["dir"], $this->config_path["menu"]["name"]);
    }

    public function configUpdateMenu(string $type, array $contents)
    {
        return $this->jsonUpdateDataSimple($type, $this->config_path["menu"]["dir"], $this->config_path["menu"]["name"], [], $contents);
    }

    public function configRemoveFromMenu(string $type)
    {
        return $this->jsonRemoveDataSimple($type, $this->config_path["menu"]["dir"], $this->config_path["menu"]["name"]);
    }
}