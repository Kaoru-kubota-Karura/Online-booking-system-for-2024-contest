-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost:8889
-- 生成日時: 2024 年 8 月 25 日 02:11
-- サーバのバージョン： 5.7.39
-- PHP のバージョン: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `contest2024`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `data`
--

-- CREATE TABLE `data` (
--   `user` varchar(11) NOT NULL,
--   `pass` int(11) NOT NULL,
--   `coin` int(11) NOT NULL,
--   `t-hour` float NOT NULL,
--   `next` date NOT NULL,
--   `n-hour` float NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `reservation_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `teacher`, `reservation_time`) VALUES
(20, 1, 'Smith', '2024-08-11 11:00:00'),
(21, 1, 'David', '2024-08-11 11:30:00'),
(22, 1, 'Smith', '2024-08-12 00:00:00'),
(23, 2, 'Smith', '2024-08-11 12:00:00'),
(24, 2, 'Smith', '2024-08-11 13:00:00'),
(25, 1, 'Smith', '2024-08-12 12:00:00'),
(26, 1, 'Smith', '2024-08-11 14:00:00'),
(29, 1, 'Smith', '2024-08-18 11:30:00'),
(30, 1, 'Tom', '2024-08-18 12:00:00'),
(32, 1, 'Tom', '2024-08-18 14:00:00'),
(33, 1, 'Smith', '2024-08-19 14:30:00'),
(34, 1, 'Tom', '2024-09-04 00:00:00'),
(35, 1, 'Mat', '2024-08-21 12:00:00'),
(36, 1, 'Mat', '2024-09-06 12:00:00');

-- --------------------------------------------------------

--
-- テーブルの構造 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'a', '$2y$10$f7Y/aphYvfl6PMY6OJSzb.MS9bRG4KVbldNrnfjuZsyiQY.aiTDzm'),
(2, 'b', '$2y$10$OguW7yk0xGdRTZ.pqpLZcO5wU1LL6Wtcft3smA91BwYeob/JEKVfC');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- テーブルの AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
