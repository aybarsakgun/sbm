-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 15 Nis 2020, 00:36:27
-- Sunucu sürümü: 10.2.10-MariaDB
-- PHP Sürümü: 7.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `sbm`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `mail` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `password` char(128) COLLATE utf8mb4_turkish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `admin`
--

INSERT INTO `admin` (`id`, `mail`, `password`) VALUES
(1, 'admin@admin.admin', '$2y$10$jV1J4ne4g4lBhI9DsTfGOOz2G9.ggrZ2D2z.9F48Xklp.X2N3JGqq');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `detail` text COLLATE utf8mb4_turkish_ci NOT NULL,
  `date` datetime NOT NULL,
  `admin` int(11) NOT NULL,
  `school` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `school` int(11) NOT NULL,
  `teachers` varchar(256) COLLATE utf8mb4_turkish_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `gc_id` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `color` varchar(32) COLLATE utf8mb4_turkish_ci NOT NULL,
  `student_show` int(11) NOT NULL DEFAULT 1,
  `point_show` int(11) NOT NULL DEFAULT 1,
  `status` int(11) NOT NULL DEFAULT 1,
  `points_by_time` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `first` int(11) NOT NULL,
  `second` int(11) NOT NULL,
  `first_deleted` int(11) NOT NULL DEFAULT 0,
  `second_deleted` int(11) NOT NULL DEFAULT 0,
  `class_name` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `point` varchar(32) COLLATE utf8mb4_turkish_ci NOT NULL,
  `type` int(11) NOT NULL,
  `user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `feedbacks_students`
--

CREATE TABLE `feedbacks_students` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `point` varchar(32) COLLATE utf8mb4_turkish_ci NOT NULL,
  `type` int(11) NOT NULL,
  `description` text COLLATE utf8mb4_turkish_ci NOT NULL,
  `teacher` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `point_location` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `class` int(11) NOT NULL,
  `school` int(11) NOT NULL,
  `students` varchar(255) COLLATE utf8mb4_turkish_ci NOT NULL,
  `created_time` datetime NOT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `invited_teachers`
--

CREATE TABLE `invited_teachers` (
  `id` int(11) NOT NULL,
  `class` int(11) NOT NULL,
  `inviting_by` int(11) NOT NULL,
  `invited` int(11) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `login_attempts`
--

CREATE TABLE `login_attempts` (
  `member_id` int(11) NOT NULL,
  `date` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `ip` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `browser` varchar(256) COLLATE utf8mb4_turkish_ci NOT NULL,
  `date_time` datetime NOT NULL,
  `status` int(11) NOT NULL,
  `verify` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `login_attempts_user`
--

CREATE TABLE `login_attempts_user` (
  `member_id` int(11) NOT NULL,
  `date` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `ip` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `browser` varchar(256) COLLATE utf8mb4_turkish_ci NOT NULL,
  `platform` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `date_time` datetime NOT NULL,
  `status` int(11) NOT NULL,
  `verify` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation` int(11) NOT NULL,
  `user_from` int(11) NOT NULL,
  `user_to` int(11) NOT NULL,
  `from_deleted` int(11) NOT NULL DEFAULT 0,
  `to_deleted` int(11) NOT NULL DEFAULT 0,
  `seen` datetime DEFAULT NULL,
  `sent` datetime DEFAULT NULL,
  `message` text COLLATE utf8mb4_turkish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `message_templates`
--

CREATE TABLE `message_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `text` text COLLATE utf8mb4_turkish_ci NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `point_locations`
--

CREATE TABLE `point_locations` (
  `id` int(11) NOT NULL,
  `school` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `redeem_items`
--

CREATE TABLE `redeem_items` (
  `id` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_turkish_ci NOT NULL,
  `user` int(11) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `schools`
--

CREATE TABLE `schools` (
  `id` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_turkish_ci NOT NULL,
  `date_type` int(11) NOT NULL DEFAULT 1,
  `quarter1st` date DEFAULT NULL,
  `quarter2st` date DEFAULT NULL,
  `quarter3st` date DEFAULT NULL,
  `quarter4st` date DEFAULT NULL,
  `quarter1fn` date DEFAULT NULL,
  `quarter2fn` date DEFAULT NULL,
  `quarter3fn` date DEFAULT NULL,
  `quarter4fn` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(25) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `name` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `email` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `classes` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `schools` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `role` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `invite_token` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL,
  `invite_date` datetime NOT NULL,
  `register_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `avatar` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL,
  `register_type` int(11) DEFAULT NULL,
  `parent_name` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `parent_email` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `parent_email2` varchar(128) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `parent_phone` varchar(32) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `parent_phone2` varchar(32) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `homeroom` varchar(64) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `gender` varchar(32) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `stateID` varchar(32) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT '',
  `grade` varchar(32) COLLATE utf8mb4_turkish_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `feedbacks_students`
--
ALTER TABLE `feedbacks_students`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `invited_teachers`
--
ALTER TABLE `invited_teachers`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `message_templates`
--
ALTER TABLE `message_templates`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `point_locations`
--
ALTER TABLE `point_locations`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `redeem_items`
--
ALTER TABLE `redeem_items`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `feedbacks_students`
--
ALTER TABLE `feedbacks_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `invited_teachers`
--
ALTER TABLE `invited_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `message_templates`
--
ALTER TABLE `message_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `point_locations`
--
ALTER TABLE `point_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `redeem_items`
--
ALTER TABLE `redeem_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
