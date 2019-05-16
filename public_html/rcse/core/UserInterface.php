<?php
declare(strict_types=1);
namespace RCSE\Core;

/** Provides functions to create and control an user interface */
class UserInterface
{
    /** @var File */
    private $file;

    /** @var Logger */
    private $logger;

    /** @var Configurator */
    private $config;

    private $page_elements = [];
    private $page_contents;
    private $theme_dir, $pages_dir, $locales_dir;

    public function __construct(Logger $logger, Configurator $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->file = new File();

        $this->uiInitPage();
    }

    private function uiInitPage()
    {
        $this->theme_dir = "/resources/themes/" . $this->config->configObtainMain("site")['theme'] ."/";
        $this->pages_dir = $this->theme_dir . "pages/";
        $this->locales_dir = $this->theme_dir . "locale/";
        $page_locale = $this->config->configObtainMain("site")['lang'];
        $this->page_contents = $this->file->fileRead($this->pages_dir, "structure.html");
        
        
        $this->uiSetPageElement("LANG", $page_locale);
        $this->uiSetPageElement("PAGE_STYLE", $this->pages_dir . "structure/structure.css");
        $this->uiSetPageElement("MAIN_MENU", $this->uiGenerateMenu());
    }

    public function uiGeneratePage()
    {
        $page = $this->page_contents;

        foreach($this->page_elements as $key => $value) {
            $key = strtoupper($key);
            $page = str_replace("[{$key}]", $value, $page);
        }

        $this->page_contents = $page;

        $this->uiLocalize($this->config->configObtainMain('site')['lang']);

        print($this->page_contents);
    }

    protected function uiLocalize(string $lang)
    {
        $locale_by_theme = $this->config->configObtainLocale($lang, 'interface', $this->config->configObtainMain('site')['theme']);
        $locale = $this->config->configObtainLocale($lang, 'interface', 'engine');
        $locale = array_merge($locale, $locale_by_theme);
        $page = $this->page_contents;

        foreach($locale as $key => $value) {
            $page = str_replace("*{$key}*", $value, $page);
        }

        $this->page_contents = $page;
    }

    protected function uiGenerateMenu()
    {
        $menu_raw = $this->config->configObtainMenu();

        $menu = "<ul>";
        
        foreach($menu_raw as $key => $value) {
            $menu .= "<li class=\"menu_button\"><a href=\"{$value}\"><span class=\"menu_button_text\"><span>*{$key}*</span></span><span class=\"menu_button_icon {$key}\"></span></a></li>";
        }

        $menu .= "</ul>";

        return $menu;
    }

    public function uiSetPageElement(string $element, string $content)
    {
        $this->page_elements[$element] = $content;
    }    

    public function uiCreateUserPage(string $login)
    {
        $user_data = $this->user->userGetInfo($login);
    }

}