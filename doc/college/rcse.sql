-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Дек 06 2018 г., 14:55
-- Версия сервера: 8.0.12
-- Версия PHP: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `rcse`
--

-- --------------------------------------------------------

--
-- Структура таблицы `accounts`
--

CREATE TABLE `accounts` (
  `login` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(127) NOT NULL,
  `sex` varchar(1) DEFAULT NULL,
  `brithdate` varchar(10) DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `regdate` varchar(10) NOT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `accounts`
--

INSERT INTO `accounts` (`login`, `password`, `email`, `sex`, `brithdate`, `origin`, `regdate`, `settings`) VALUES
('Test', 'hsffgsdr34r23f312121', 'test@ya.x', 'm', '0000-00-00', 'MSK', '2018-11-25', '{}');

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(10) UNSIGNED NOT NULL,
  `reply_to` int(10) UNSIGNED NOT NULL,
  `date` varchar(19) NOT NULL,
  `author` varchar(20) NOT NULL,
  `voteups` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `votedowns` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `posts`
--

CREATE TABLE `posts` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(45) NOT NULL,
  `date` varchar(16) NOT NULL,
  `author` varchar(20) NOT NULL,
  `voteups` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `votedowns` int(10) UNSIGNED NOT NULL,
  `description` varchar(127) DEFAULT NULL,
  `tags` text,
  `content` text NOT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `posts`
--

INSERT INTO `posts` (`post_id`, `title`, `date`, `author`, `voteups`, `votedowns`, `description`, `tags`, `content`, `settings`) VALUES
(0, 'Hello world!', '2018-12-06', 'Test', 0, 0, NULL, NULL, 'Hello there! Let\'s see a trailer! <br>\r\n<video width=\"512px\" controls><source src=\"/tlk.mp4\"></video>', '{}');

-- --------------------------------------------------------

--
-- Структура таблицы `punishments`
--

CREATE TABLE `punishments` (
  `login` varchar(20) NOT NULL,
  `type` varchar(8) NOT NULL,
  `reason` text NOT NULL,
  `rule` varchar(8) NOT NULL,
  `date` varchar(15) NOT NULL,
  `expires` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `replies`
--

CREATE TABLE `replies` (
  `reply_id` int(10) UNSIGNED NOT NULL,
  `reply_to` int(10) UNSIGNED NOT NULL,
  `date` varchar(18) NOT NULL,
  `author` varchar(20) NOT NULL,
  `voteups` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `votedowns` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `topics`
--

CREATE TABLE `topics` (
  `topic_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(45) NOT NULL,
  `section` varchar(45) NOT NULL,
  `date` varchar(19) NOT NULL,
  `author` varchar(20) NOT NULL,
  `voteups` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `votedowns` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `tags` text,
  `content` text NOT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`login`),
  ADD UNIQUE KEY `login_UNIQUE` (`login`),
  ADD UNIQUE KEY `password_UNIQUE` (`password`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD UNIQUE KEY `comment_id_UNIQUE` (`comment_id`),
  ADD KEY `login_idx` (`author`),
  ADD KEY `post_id_idx` (`reply_to`);

--
-- Индексы таблицы `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `post_id_UNIQUE` (`post_id`),
  ADD KEY `login_idx` (`author`);

--
-- Индексы таблицы `punishments`
--
ALTER TABLE `punishments`
  ADD PRIMARY KEY (`login`);

--
-- Индексы таблицы `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD UNIQUE KEY `reply_id_UNIQUE` (`reply_id`),
  ADD KEY `login_idx` (`author`),
  ADD KEY `topic_id_idx` (`reply_to`);

--
-- Индексы таблицы `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD UNIQUE KEY `topic_id_UNIQUE` (`topic_id`),
  ADD KEY `login_idx` (`author`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `replies`
--
ALTER TABLE `replies`
  MODIFY `reply_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `topics`
--
ALTER TABLE `topics`
  MODIFY `topic_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comment_id` FOREIGN KEY (`reply_to`) REFERENCES `comments` (`comment_id`),
  ADD CONSTRAINT `login_comments` FOREIGN KEY (`author`) REFERENCES `accounts` (`login`),
  ADD CONSTRAINT `post_id` FOREIGN KEY (`reply_to`) REFERENCES `posts` (`post_id`);

--
-- Ограничения внешнего ключа таблицы `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `author` FOREIGN KEY (`author`) REFERENCES `accounts` (`login`);

--
-- Ограничения внешнего ключа таблицы `punishments`
--
ALTER TABLE `punishments`
  ADD CONSTRAINT `login` FOREIGN KEY (`login`) REFERENCES `accounts` (`login`);

--
-- Ограничения внешнего ключа таблицы `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `login_replies` FOREIGN KEY (`author`) REFERENCES `accounts` (`login`),
  ADD CONSTRAINT `reply_id` FOREIGN KEY (`reply_to`) REFERENCES `replies` (`reply_id`),
  ADD CONSTRAINT `topic_id` FOREIGN KEY (`reply_to`) REFERENCES `topics` (`topic_id`);

--
-- Ограничения внешнего ключа таблицы `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `login_topics` FOREIGN KEY (`author`) REFERENCES `accounts` (`login`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
