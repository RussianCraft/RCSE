<?php

if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

require ROOT . "/vendor/autoload.php";

$logger = new RCSE\Core\Logger();
$config = new RCSE\Core\Configurator($logger);
$ui = new RCSE\Core\UserInterface($logger, $config);
$user = new RCSE\Core\User($logger, $config);

if(!empty($_GET)) {
    if (isset($_GET['page'])) {
        $page = htmlspecialchars($_GET['page']);
    }
    if (isset($_GET['mode'])) {
        $mode = htmlspecialchars($_GET['mode]']);
    }
}

//Page general structure setup
if($user->userIsSessionSet()) {
    $user_menu = "<ul>
                    <li class=\"account_menu_button first\"><span class=\"account_menu_button_text\"><a href=\"/?page=user\">Личный кабинет</a></span></li>
                    <li class=\"account_menu_button\"><span class=\"account_menu_button_text\"><a href=\"/?page=user&mode=settings\">Настройки</a></span></li>
                    <li class=\"account_menu_button\"><span class=\"account_menu_button_text\"><a href=\"/?page=faq\">Вопросы</a></span></li>
                    <li class=\"account_menu_button last\"><span class=\"account_menu_button_text\"><a href=\"/?page=user&mode=exit\">Выход</a></span></li>
                </ul>";
    $user_name = $_COOKIE['session_login'];
} else {
    $user_menu = "<p>
                    <a href=\"/?page=user&mode=login\">Войти</a><br>или<br><a href=\"/?page=user&mode=register\">зарегестрироваться</a>
                </p>";
    $user_name = "Offline";
}

$ui->uiSetPageElement("USER_NAME", $user_name);
$ui->uiSetPageElement("USER_MENU", $user_menu);
//!Page general structure setup

//Page data setup
switch($page) {
    case 'user':
        switch($mode) {
            case 'settings':

                break;
            case 'exit':

                break;
            case 'login':

                break;
            case 'register':

                break;
        }

        break;
    case 'news':
        

        break;
    case 'forum':
        
        break;
    case 'faq':
        
        break;
    case 'home':
    default:

        break;
}
//!Page data setup

//Page render
$ui->uiGeneratePage();


