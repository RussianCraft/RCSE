<?php

Configurator extends JSONParser {
    public configObtainMain(string $type)
    public configUpdateMain(string $type, array $contents) : bool
    public configObtainQueries(string $table)
    public configObtainModuleProps(string $module)
    public configUpdateModuleProps(string $module, array $contents) : bool
    public configObtainLocale(string $lang, string $element, string $source)
    public configObtainUsergroup(string $group)
    public configUpdateUsergroup(string $group, array $contents)
    public configRemoveUsergroup(string $group)
    public configObtainWords(string $type)
    public configUpdateWords(string $type, array $contents)
    public configObtainSection(string $type)
    public configUpdateSection(string $type, array $contents)
    public configRemoveSection(string $type)
    public configObtainBan(string $type)
    public configUpdateBan(string $type, array $contents)
    public configRemoveBan(string $type)
    public configObtainMenu()
    public configUpdateMenu(string $type, array $contents)
    public configRemoveFromMenu(string $type)
}