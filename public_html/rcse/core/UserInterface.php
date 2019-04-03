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

    public function __construct()
    {
        $this->logger = new Logger();
        $this->config = new Configurator($this->logger);
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
        $this->uiSetPageElement("MENU", $this->uiGenerateMenu());
    }

    public function uiGeneratePage()
    {
        $page = $this->page_contents;

        foreach($this->page_elements as $key => $value) {
            $key = strtoupper($key);
            $page = str_replace("[{$key}]", $value, $page);
        }

        print($page);
    }

    protected function uiLocalize(string $lang)
    {
        $locale_by_theme = $this->config->configObtainLocale($lang, 'interface', $this->config->configObtainMain('site')['theme']);
        $locale = $this->config->configObtainLocale($lang, 'interface', 'engine');
        $locale = array_merge($locale, $locale_by_theme);

        foreach($locale as $key => $value) {
            $this->page_contents = str_replace("\\{$key}\\", $value, $this->page_contents);
        }
    }

    protected function uiGenerateMenu()
    {
        $menu_raw = $this->config->configObtainMenu();

        $menu = "<menu>";
        
        foreach($menu_raw as $key => $value) {
            $menu .= "<li><a class=\"text {$key}\" href=\"{$value}\">\\{$key}\\</a><a class=\"icon {$key}\" href=\"{$value}\"><\a></li>";
        }

        $menu .= "</menu>";

        return $menu;
    }

    public function uiSetPageElement(string $element, string $content)
    {
        $this->page_elements[$element] = $content;
    }    


}