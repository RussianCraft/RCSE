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
        $mode = htmlspecialchars($_GET['mode']);
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
                $user->userSessionEnd();
                $data = "<h1>Выход из аккаунта...</h1>
                <script>
                document.addEventListener(\"DOMContentLoaded\", function(event) {
                    window.location.href = \"/\";
                });
                </script>";
                $ui->uiSetPageElement("PAGE_CONTENT", $data);
                $ui->uiSetPageElement("PAGE_TITLE", "Выход из аккаунта");
                break;
            case 'login':
                $ui->uiCreateUserLoginPage();
                $ui->uiSetPageElement("PAGE_TITLE", "Вход в аккаунт");
                break;
            case 'register':

                break;
            default:

                break;
        }

        break;
    case 'news':
        

        break;
    case 'forum':
        
        break;
    case 'faq':
        
        break;
    case 'auth':
        switch($mode){
            case 'login':
                $id = htmlspecialchars($_POST['id']);
                $pass = htmlspecialchars($_POST['password']);
                if($_POST['save_session'] === null) $save_session = false;
                else $save_session = $_POST['save_session'];
                $login = $user->userLogin($id, $pass, $save_session);
                if($login !== true) {
                    $data = "
                    <form action='/?page=error' id='form' method='POST'>
                        <input type='hidden' name='message' value='{$login}'
                    </form>
                    <script>
                        document.getElementById('form').submit();
                    </script>";
                } else {
                    $data = "
                    <script>
                        window.location.href = '/';
                    </script>";
                }
                $ui->uiSetPageElement("PAGE_CONTENT", $data);
                break;
            case 'register':

                break;
        }
        break;
    case 'error':

        break;
    case 'home':
    default:
        $data = "<h1>No content yet!</h1>";
        break;
}

//!Page data setup

//Page render
$ui->uiGeneratePage();


