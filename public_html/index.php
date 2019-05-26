<?php
if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

require ROOT . "/vendor/autoload.php";

use RCSE\Core\Logger;
use RCSE\Core\Configurator;
use RCSE\Core\UserInterface;
use RCSE\Core\User;
use RCSE\Core\Forum;
use RCSE\Core\NewsPost;

$logger = new Logger();
$config = new Configurator($logger);
$ui = new UserInterface($logger, $config);
$user = new User($logger, $config);
$news = new NewsPost($logger, $config);
$forum = new Forum($logger, $config);

if (!empty($_GET)) {
    if (isset($_GET['page'])) {
        $page = htmlspecialchars($_GET['page']);
    }
    if (isset($_GET['mode'])) {
        $mode = htmlspecialchars($_GET['mode']);
    }
}

//Page general structure setup
$user_online = $user->userIsSessionSet();
if ($user_online) {
    $user_menu = "<ul>
                    <li class=\"account_menu_button first\"><span class=\"account_menu_button_text\"><a href=\"/?page=user\">Личный кабинет</a></span></li>
                    <li class=\"account_menu_button\"><span class=\"account_menu_button_text\"><a href=\"/?page=user&mode=settings\">Настройки</a></span></li>
                    <li class=\"account_menu_button\"><span class=\"account_menu_button_text\"><a href=\"/?page=faq\">Вопросы</a></span></li>
                    <li class=\"account_menu_button last\"><span class=\"account_menu_button_text\"><a href=\"/?page=user&mode=exit\">Выход</a></span></li>
                </ul>";
    $user_name = $_COOKIE['session_login'];
} else {
    $user_menu = "<p>
                    <a href=\"/?page=user&mode=login\">Войти</a><br>или<br><a href=\"/?page=user&mode=register\">зарегистрироваться</a>
                </p>";
    $user_name = "Offline";
}

$ui->uiSetPageElement("USER_NAME", $user_name);
$ui->uiSetPageElement("USER_MENU", $user_menu);
//!Page general structure setup

//Page data setup
switch ($page) {
    case 'user':
        switch ($mode) {
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
                $ui->uiCreateUserRegPage();
                $ui->uiSetPageElement("PAGE_TITLE", "Создание аккаунта");
                break;
            default:
                if(isset($_GET['login'])) {
                    $login = htmlspecialchars($_GET['login']);
                } elseif (isset($_COOKIE['session_login'])) {
                    $login = $_COOKIE['session_login'];
                } else {
                    $page_cont = "<script>window.location.href = '/' </script>";
                    $ui->uiSetPageElement("PAGE_CONTENT", $page_cont);
                }
                
                $user_data = $user->userGetInfo($login);

                $email = ($_COOKIE['session_login'] === $login) ? $user_data['email'] : "скрыто";
                $birthday = ($_COOKIE['session_login'] === $login) ? $user_data['birthdate'] : "скрыто";
                $origin = ($_COOKIE['session_login'] === $login) ? $user_data['origin'] : "скрыто";
                $sex = ($_COOKIE['session_login'] === $login) ? $user_data['sex'] : "скрыто";
                $reg_day = $user_data['regdate'];
        
                $ui->uiSetPageElement("USER_LOGIN", $login);
                $ui->uiSetPageElement("USER_EMAIL", $email);
                $ui->uiSetPageElement("USER_BDAY", $birthday);
                $ui->uiSetPageElement("USER_ORIGIN", $origin);
                $ui->uiSetPageElement("USER_SEX", $sex);
                $ui->uiSetPageElement("USER_RDAY", $reg_day);

                $ui->uiCreateUserPage();
                break;
        }
        break;
    case 'news':
        switch ($mode) {
            case 'create':
                if ($user_online === false) {
                    $page_contents = "<script>window.location.href = '/?page=news' </script>";
                    $ui->uiSetPageElement("PAGE_CONTENT", $page_contents);
                }

                $ui->uiCreatePostCreationPage();
                $ui->uiSetPageElement("PAGE_TITLE", "Создание публикации");
                break;
            case 'view':
                if (isset($_GET['id']) === false) {
                    $page_cont = "<script>window.location.href = '/?page=news'; </script>";
                    $ui->uiSetPageElement("PAGE_CONTENT", $page_cont);
                    break;
                }
                $id = htmlspecialchars($_GET['id']);

                $ui->uiCreatePostPage();

                $posts = $news->newsPostGetPostsList();

                $ui->uiSetPageElement("PAGE_TITLE", "Просмотр: " . $posts[$id]['title']);
                $ui->uiSetPageElement("POST_TITLE", $posts[$id]['title']);
                $ui->uiSetPageElement("POST_AUTHOR", $posts[$id]['author']);
                $ui->uiSetPageElement("POST_DATE", $posts[$id]['date']);
                $ui->uiSetPageElement("POST_VOTEUP", $posts[$id]['voteups']);
                $ui->uiSetPageElement("POST_VOTEDOWN", $posts[$id]['votedowns']);
                $ui->uiSetPageElement("POST_DESCRIPTION", $posts[$id]['description']);
                $ui->uiSetPageElement("POST_CONTENT", $posts[$id]['content']);

                $post_count = count($posts);

                if ($post_count === 0) {
                    $post_block = "<div class=\"news_post\">
                                <div class=\"news_post_image\">
                                    <span>*</span>
                                </div>
                                <div class=\"news_post_text\">
                                    <span class=\"news_post_title\"><a nohref>Здесь пусто</a></span>
                                    <span class=\"news_post_descr\">Пока других новостей нет.</span>
                                </div>
                            </div>";
                } elseif ($post_count >= 4) {
                    $post_block = "";
                    for ($i = 1; $i < 4; $i++) {
                        if ($i == $id) {
                            $a = 5;
                            $post_block .= "<div class=\"news_post\">
                                        <div class=\"news_post_image\">
                                            <span>Изображения нет</span>
                                        </div>
                                        <div class=\"news_post_text\">
                                            <span class=\"news_post_title\"><a href=\"/?page=news&mode=view&id={$posts[$a]['post_id']}\">{$posts[$a]['title']}</a></span>
                                            <span class=\"news_post_descr\">{$posts[$a]['description']}</span>
                                        </div>
                                    </div>";
                        }
                        $post_block .= "<div class=\"news_post\">
                                    <div class=\"news_post_image\">
                                        <span>Изображения нет</span>
                                    </div>
                                    <div class=\"news_post_text\">
                                        <span class=\"news_post_title\"><a href=\"/?page=news&mode=view&id={$posts[$i]['post_id']}\">{$posts[$i]['title']}</a></span>
                                        <span class=\"news_post_descr\">{$posts[$i]['description']}</span>
                                    </div>
                                </div>";
                    }
                } else {
                    $post_block = "";
                    for ($i = 0; $i < $post_count; $i++) {
                        $post_block .= "<div class=\"news_post\">
                                    <div class=\"news_post_image\">
                                        <span>Изображения нет</span>
                                    </div>
                                    <div class=\"news_post_text\">
                                        <span class=\"news_post_title\"><a href=\"/?page=news&mode=view&id={$posts[$i]['post_id']}\">{$posts[$i]['title']}</a></span>
                                        <span class=\"news_post_descr\">{$posts[$i]['description']}</span>
                                    </div>
                                </div>";
                    }
                }
                $ui->uiSetPageElement("POST_BLOCK", $post_block);
                break;
            default:
                $ui->uiCreateNewsPage();

                $posts = $news->newsPostGetPostsList();
                $post_count = count($posts);

                if ($post_count === 0) {
                    if ($user_online) {
                        $post_block = "<div class=\"news_post\">
                                <div class=\"news_post_image\">
                                    <span>*</span>
                                </div>
                                <div class=\"news_post_text\">
                                    <span class=\"news_post_title\"><a href=\"/?page=news&mode=create\">Создать новую публикацию</a></span>
                                    <span class=\"news_post_descr\">Пока нововстей нет. Исправте это!</span>
                                </div>
                            </div>";
                    } else {
                        $post_block = "<div class=\"news_post\">
                        <div class=\"news_post_image\">
                            <span>*</span>
                        </div>
                        <div class=\"news_post_text\">
                            <span class=\"news_post_title\"><a nohref>Здесь пусто</a></span>
                            <span class=\"news_post_descr\">Пока других новостей нет.</span>
                        </div>
                    </div>";
                    }
                } else {
                    $post_block = "";
                    for ($i = 0; $i < $post_count; $i++) {
                        $post_block .= "<div class=\"news_post\">
                                    <div class=\"news_post_image\">
                                        <span>Изображения нет</span>
                                    </div>
                                    <div class=\"news_post_text\">
                                        <span class=\"news_post_title\"><a href=\"/?page=news&mode=view&id={$posts[$i]['post_id']}\">{$posts[$i]['title']}</a></span>
                                        <span class=\"news_post_descr\">{$posts[$i]['description']}</span>
                                    </div>
                                </div>";
                    }

                    if ($user_online) {
                        $post_block .= "<div class=\"news_post\">
                                            <div class=\"news_post_image\">
                                                <span>*</span>
                                            </div>
                                            <div class=\"news_post_text\">
                                                <span class=\"news_post_title\"><a href=\"/?page=news&mode=create\">Создать новую публикацию</a></span>
                                                <span class=\"news_post_descr\">Расскажите что-нибудь новое!</span>
                                            </div>
                                        </div>";
                    }
                }
                $ui->uiSetPageElement("POST_BLOCK", $post_block);
                $ui->uiSetPageElement("PAGE_TITLE", "Публикации");
                break;
        }
        break;
    case 'forum':
        switch ($mode) {
            case 'create':
                if ($user_online === false) {
                    $page_contents = "<script>window.location.href = '/?page=news' </script>";
                    $ui->uiSetPageElement("PAGE_CONTENT", $page_contents);
                }

                $sections = $forum->forumGetSectionsList();
                $sections_count = (empty($sections)) ? 0 : count($sections);

                if ($sections_count === 0) {
                    $sections_block = "<select name=\"section\" required>
                                            <option disabled>Разделов нет</option>
                                        </select>";
                } else {
                    $sections_block = "<select name=\"section\" required>
                                            <option disabled selected>Выберите раздел</option>";
                    foreach($sections as $key => $value) {
                        $sections_block .= "<option value=\"{$key}\">{$sections[$key]['name']}</option>";
                    }
                    $sections_block .= "</select>";
                }

                $ui->uiCreateTopicCreationPage();
                $ui->uiSetPageElement("SECTIONS_BLOCK", $sections_block);
                $ui->uiSetPageElement("PAGE_TITLE", "Создание публикации");
                break;
            case 'view':
                if (isset($_GET['id']) === false) {
                    $page_cont = "<script>window.location.href = '/?page=news'; </script>";
                    $ui->uiSetPageElement("PAGE_CONTENT", $page_cont);
                    break;
                }
                $id = htmlspecialchars($_GET['id']);

                $ui->uiCreateTopicPage();

                $topic = $forum->forumGetTopicData($id);

                $ui->uiSetPageElement("PAGE_TITLE", "Просмотр: " . $topic['title']);
                $ui->uiSetPageElement("TOPIC_TITLE", $topic['title']);
                $ui->uiSetPageElement("TOPIC_AUTHOR", $topic['author']);
                $ui->uiSetPageElement("TOPIC_DATE", $topic['date']);
                $ui->uiSetPageElement("TOPIC_VOTEUP", $topic['voteups']);
                $ui->uiSetPageElement("TOPIC_VOTEDOWN", $topic['votedowns']);
                $ui->uiSetPageElement("TOPIC_CONTENT", $topic['content']);

                break;
            default:
                $ui->uiCreateForumPage();

                $sections = $forum->forumGetSectionsList();
                $sections_count = (empty($sections)) ? 0 : count($sections);

                if ($sections_count === 0) {
                    $page_data = "<header class=\"forum_section_header\">
                                    <span class=\"forum_section_title\">Похоже, разделов нет</span>
                                </header>";
                    $ui->uiSetPageElement("PAGE_CONTENT", $page_data);
                } else {
                    $page_data = "";
                    foreach ($sections as $key => $value) {

                        $page_data .= "<header class=\"forum_section_header\">
                                            <span class=\"forum_section_title\">{$sections[$key]['name']}</span>
                                        </header>
                                        <main class=\"forum_section_main\">";
                        $topics = $forum->forumGetTopicsList($key);
                        $topics_count = (empty($topics)) ? 0 : count($topics);

                        if ($topics_count === 0) {
                            if ($user_online) { 
                                $page_data .= "<div class=\"forum_post\">
                                                <div class=\"forum_post_text\">
                                                    <span class=\"forum_post_title\"><a href=\"/?page=forum&mode=create\">Создать пост</a></span>
                                                    <div class=\"forum_post_info\">
                                                        <span class=\"author_name\"><a nohref>-</a></span>
                                                        <div class=\"forum_post_votes\">
                                                            <span class=\"vote_positive\">-</span>
                                                            <span class=\"vote_negative\">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>";
                            } else {
                                $page_data .= "<div class=\"forum_post\">
                                                <div class=\"forum_post_text\">
                                                    <span class=\"forum_post_title\">Похоже, на форуме пусто</span>
                                                </div>
                                            </div>";
                            }
                        } else {
                            for ($i = 0; $i < $topics_count; $i++) {
                                $page_data .= "<div class=\"forum_post\">
                                                    <div class=\"forum_post_text\">
                                                        <span class=\"forum_post_title\"><a href=\"/?page=forum&mode=view&id={$topics[$i]['topic_id']}\">{$topics[$i]['title']}</a></span>
                                                        <div class=\"forum_post_info\">
                                                            <span class=\"author_name\"><a href=\"/?page=user&login={$topics[$i]['author']}\">{$topics[$i]['author']}</a></span>
                                                            <div class=\"forum_post_votes\">
                                                                <span class=\"vote_positive\">{$topics[$i]['voteups']}</span>
                                                                <span class=\"vote_negative\">{$topics[$i]['votedowns']}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>";
                            }
                            $page_data .= "<div class=\"forum_post\">
                                                <div class=\"forum_post_text\">
                                                    <span class=\"forum_post_title\"><a href=\"/?page=forum&mode=create\">Создать пост</a></span>
                                                    <div class=\"forum_post_info\">
                                                        <span class=\"author_name\"><a nohref>-</a></span>
                                                        <div class=\"forum_post_votes\">
                                                            <span class=\"vote_positive\">-</span>
                                                            <span class=\"vote_negative\">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>";
                        }

                        $page_data .= "</main>";
                    }
                }
                $ui->uiSetPageElement("FORUM_CONTENT", $page_data);
                $ui->uiSetPageElement("PAGE_TITLE", "Форум");
                break;
        }
        break;
    case 'faq':

        break;
    case 'rules':

        break;
    case 'auth':
        switch ($mode) {
            case 'login':
                $id = htmlspecialchars($_POST['id']);
                $pass = htmlspecialchars($_POST['password']);
                if ($_POST['save_session'] === null) $save_session = false;
                else $save_session = $_POST['save_session'];
                $login = $user->userLogin($id, $pass, $save_session);
                if ($login !== true) {
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
                $login = htmlspecialchars($_POST['login']);
                $pass = htmlspecialchars($_POST['password']);
                $email = htmlspecialchars($_POST['email']);
                $sex = htmlspecialchars($_POST['sex']);

                if (isset($_POST['birthdate'])) $birthdate = htmlspecialchars($_POST['birthdate']);
                else $birthdate = null;

                if (isset($_POST['origin'])) $origin = htmlspecialchars($_POST['origin']);
                else $origin = null;

                $reg = $user->userRegister($login, $pass, $email, $sex, $birthdate, $origin);
                if ($reg !== true) {
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
        }
        break;
    case 'action':
        switch ($mode) {
            case 'news_post':
                if ($user_online === false) {
                    $page_contents = "<script>window.location.href = '/?page=news' </script>";
                    $ui->uiSetPageElement("PAGE_CONTENT", $page_contents);
                }

                $title = htmlspecialchars($_POST['title']);
                $description = htmlspecialchars($_POST['description']);
                $content = htmlspecialchars($_POST['contents']);
                $tags = htmlspecialchars($_POST['tags']);

                $res = $news->newsPostCreatePost($title, $_COOKIE['session_login'], $description, $tags, $content);
                if ($res !== true) {
                    $data = "
                    <form action='/?page=error' id='form' method='POST'>
                        <input type='hidden' name='message' value='{$title}'
                    </form>
                    <script>
                        document.getElementById('form').submit();
                    </script>";
                } else {
                    $data = "
                    <script>
                        window.location.href = '/?page=news';
                    </script>";
                }
                $ui->uiSetPageElement("PAGE_CONTENT", $data);
                break;
            case 'news_upvote':

                break;
            case 'news_downvote':

                break;
            case 'news_edit':

                break;
            case 'news_remove':

                break;
            case 'comment_post':

                break;
            case 'comment_upvote':

                break;
            case 'comment_downvote':

                break;
            case 'comment_edit':

                break;
            case 'comment_remove':

                break;
            case 'forum_post':
                if ($user_online === false) {
                    $page_contents = "<script>window.location.href = '/?page=forum' </script>";
                    $ui->uiSetPageElement("PAGE_CONTENT", $page_contents);
                }

                $title = htmlspecialchars($_POST['title']);
                $content = htmlspecialchars($_POST['contents']);
                $tags = htmlspecialchars($_POST['tags']);

                $res = $forum->forumCreateTopic($title, $_COOKIE['session_login'], "default", $tags, $content);
                if ($res !== true) {
                    $data = "
                        <form action='/?page=error' id='form' method='POST'>
                            <input type='hidden' name='message' value='{$title}'
                        </form>
                        <script>
                            document.getElementById('form').submit();
                        </script>";
                } else {
                    $data = "
                        <script>
                            window.location.href = '/?page=forum';
                        </script>";
                }
                $ui->uiSetPageElement("PAGE_CONTENT", $data);

                break;
            case 'forum_upvote':

                break;
            case 'forum_downvote':

                break;
            case 'forum_edit':

                break;
            case 'forum_remove':

                break;
        }
        break;
    case 'error':

        break;
    case 'home':
    default:
        $ui->uiCreateHomePage();

        $posts = $news->newsPostGetPostsList();
        $post_count = count($posts);

        if ($post_count === 0) {
            $post_block = "<div class=\"news_post\">
                                <div class=\"news_post_image\">
                                    <span>*</span>
                                </div>
                                <div class=\"news_post_text\">
                                    <span class=\"news_post_title\"><a href=\"/?page=news&mode=create\">Создать новую публикацию</a></span>
                                    <span class=\"news_post_descr\">Пока нововстей нет. Исправте это!</span>
                                </div>
                            </div>";
        } elseif ($post_count >= 4) {
            $post_block = "";
            for ($i = 0; $i < 3; $i++) {
                $post_block .= "<div class=\"news_post\">
                                    <div class=\"news_post_image\">
                                        <span>Изображения нет</span>
                                    </div>
                                    <div class=\"news_post_text\">
                                        <span class=\"news_post_title\"><a href=\"/?page=news&mode=view&id={$posts[$i]['post_id']}\">{$posts[$i]['title']}</a></span>
                                        <span class=\"news_post_descr\">{$posts[$i]['description']}</span>
                                    </div>
                                </div>";
            }
            $post_block .= "<div class=\"news_post\">
                                <div class=\"news_post_image\">
                                    <span>+</span>
                                </div>
                                <div class=\"news_post_text\">
                                    <span class=\"news_post_title\"><a href=\"/?page=news\">Все публикации</a></span>
                                    <span class=\"news_post_descr\">Новостей много. Нужно прочесть их все!</span>
                                </div>
                            </div>";
        } else {
            $post_block = "";
            for ($i = 0; $i < $post_count; $i++) {
                $post_block .= "<div class=\"news_post\">
                                    <div class=\"news_post_image\">
                                        <span>Изображения нет</span>
                                    </div>
                                    <div class=\"news_post_text\">
                                        <span class=\"news_post_title\"><a href=\"/?page=news&mode=view&id={$posts[$i]['post_id']}\">{$posts[$i]['title']}</a></span>
                                        <span class=\"news_post_descr\">{$posts[$i]['description']}</span>
                                    </div>
                                </div>";
            }
            $post_block .= "<div class=\"news_post\">
                                <div class=\"news_post_image\">
                                    <span>*</span>
                                </div>
                                <div class=\"news_post_text\">
                                    <span class=\"news_post_title\"><a href=\"/?page=news&mode=create\">Создать новую публикацию</a></span>
                                    <span class=\"news_post_descr\">Расскажите что-нибудь новое!</span>
                                </div>
                            </div>";
        }
        $ui->uiSetPageElement("POST_BLOCK", $post_block);

        $forum_posts = $forum->forumGetTopicsListAll();
        $forum_count = count($forum_posts);

        if ($forum_count === 0) {
            $forum_block = "<div class=\"forum_post\">
                                <div class=\"forum_post_text\">
                                    <span class=\"forum_post_title\">Похоже, на форуме пусто</span>
                                </div>
                            </div>";
        } elseif ($forum_count >= 4) {
            $forum_block = "";
            for ($i = 0; $i < 3; $i++) {
                $forum_block .= "<div class=\"forum_post\">
                                    <div class=\"forum_post_text\">
                                        <span class=\"forum_post_title\"><a href=\"/?page=forum&mode=view&id={$forum_posts[$i]['topic_id']}\">{$forum_posts[$i]['title']}</a></span>
                                        <div class=\"forum_post_info\">
                                            <span class=\"author_name\"><a href=\"/?page=user&login={$forum_posts[$i]['author']}\">{$forum_posts[$i]['author']}</a></span>
                                            <div class=\"forum_post_votes\">
                                                <span class=\"vote_positive\">{$forum_posts[$i]['voteups']}</span>
                                                <span class=\"vote_negative\">{$forum_posts[$i]['votedowns']}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
            }
            $forum_block .= "<div class=\"forum_post\">
                                <div class=\"forum_post_text\">
                                    <span class=\"forum_post_title\"><a href=\"/?page=forum\">На форум</a></span>
                                    <div class=\"forum_post_info\">
                                        <span class=\"author_name\"><a href=\"#\">-</a></span>
                                        <div class=\"forum_post_votes\">
                                            <span class=\"vote_positive\">-</span>
                                            <span class=\"vote_negative\">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>";
        } else {
            $forum_block = "";
            for ($i = 0; $i < $forum_count; $i++) {
                $forum_block .= "<div class=\"forum_post\">
                                    <div class=\"forum_post_text\">
                                        <span class=\"forum_post_title\"><a href=\"/?page=forum&mode=view&id={$forum_posts[$i]['topic_id']}\">{$forum_posts[$i]['title']}</a></span>
                                        <div class=\"forum_post_info\">
                                            <span class=\"author_name\"><a href=\"/?page=user&login={$forum_posts[$i]['author']}\">{$forum_posts[$i]['author']}</a></span>
                                            <div class=\"forum_post_votes\">
                                                <span class=\"vote_positive\">{$forum_posts[$i]['voteups']}</span>
                                                <span class=\"vote_negative\">{$forum_posts[$i]['votedowns']}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
            }
            $forum_block .= "<div class=\"forum_post\">
                                <div class=\"forum_post_text\">
                                    <span class=\"forum_post_title\"><a href=\"/?page=forum\">На форум</a></span>
                                    <div class=\"forum_post_info\">
                                        <span class=\"author_name\"><a href=\"#\">-</a></span>
                                        <div class=\"forum_post_votes\">
                                            <span class=\"vote_positive\">-</span>
                                            <span class=\"vote_negative\">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>";
        }
        $ui->uiSetPageElement("FORUM_BLOCK", $forum_block);

        break;
}

//!Page data setup

//Page render
$ui->uiGeneratePage();
