<?php

UserInterface {
    public __construct(Logger $logger, Configurator $config)
    private uiInitPage()
    public uiGeneratePage()
    protected uiLocalize(string $lang)
    protected uiGenerateMenu()
    public uiSetPageElement(string $element, string $content)
    public uiCreateUserPage(string $login)
    public uiCreateUserLoginPage()
}