-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Хост: 10.100.29.173:3306
-- Время создания: Май 12 2025 г., 18:04
-- Версия сервера: 10.5.25-MariaDB
-- Версия PHP: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `amocrm`
--

-- --------------------------------------------------------

--
-- Структура таблицы `info`
--

CREATE TABLE `info` (
  `info_id` int(11) NOT NULL,
  `field_amo_type` text NOT NULL,
  `field_amo_id` int(11) NOT NULL,
  `fields_info` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `info`
--

INSERT INTO `info` (`info_id`, `field_amo_type`, `field_amo_id`, `fields_info`) VALUES
(1, 'leads', 40924837, '[{\"id\":\"2171611\",\"name\":\"test\",\"values\":[{\"value\":\"121235235235\"}]},{\"id\":\"2171891\",\"name\":\"test2\",\"values\":[{\"value\":\"1\"}]},{\"id\":\"2171893\",\"name\":\"test3\",\"values\":[{\"value\":\"0\"}]}]'),
(2, 'leads', 40924837, '[{\"id\":\"2171611\",\"name\":\"test\",\"values\":[{\"value\":\"121235235235\"}]},{\"id\":\"2171891\",\"name\":\"test2\",\"values\":[{\"value\":\"1\"}]},{\"id\":\"2171893\",\"name\":\"test3\",\"values\":[{\"value\":\"0\"}]}]'),
(3, 'contacts', 67431529, '[{\"id\":\"2171565\",\"name\":\"u0422u0435u043bu0435u0444u043eu043d\",\"values\":[{\"value\":\"12121211221\",\"enum\":\"4708967\"}],\"code\":\"PHONE\"},{\"id\":\"2171895\",\"name\":\"test\",\"values\":[{\"value\":\"1211241\"}]},{\"id\":\"2171897\",\"name\":\"test2\",\"values\":[{\"value\":\"1241235235\"}]},{\"id\":\"2171899\",\"name\":\"test3\",\"values\":[{\"value\":\"9998888\"}]}]');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `info`
--
ALTER TABLE `info`
  ADD PRIMARY KEY (`info_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `info`
--
ALTER TABLE `info`
  MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
