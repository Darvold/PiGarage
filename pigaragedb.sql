-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 06, 2024 at 04:56 PM
-- Server version: 8.0.36
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pigaragedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` bigint UNSIGNED NOT NULL,
  `login` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `login`, `password`, `is_admin`) VALUES
(1, 'Darvold', '$2y$10$Pyu.fLP/IjDvm00F1erhUuD6QJGvAvB2BZmDrQ4vgxZSYox8SmA3q', 1);

-- --------------------------------------------------------

--
-- Table structure for table `applications_create_new_coops`
--

CREATE TABLE `applications_create_new_coops` (
  `id_application` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_received` datetime NOT NULL,
  `number_meter` bigint NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number_garage_blocks` int NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications_create_new_coops`
--

INSERT INTO `applications_create_new_coops` (`id_application`, `user_id`, `name`, `city`, `address`, `date_received`, `number_meter`, `status`, `number_garage_blocks`, `latitude`, `longitude`) VALUES
(1, 3, 'Тестик', 'Междуреченск', 'Кемеровская область, Междуреченск', '2024-01-05 15:16:37', 523412431, 'accepted', 0, 53.708002734377054, 88.06923885457759),
(72, 25, 'Дорога', 'Междуреченск', 'Кемеровская область, Междуреченск', '2024-08-21 21:41:44', 35236262345, 'accepted', 5, 53.6815026964252, 88.05536521372527),
(73, 3, 'Тексту341', 'Кемерово, Рудничный район', 'Кемерово, Рудничный район', '2024-08-24 13:50:38', 42151243, 'pending', 3, 55.426508095006646, 86.18499675030114);

-- --------------------------------------------------------

--
-- Table structure for table `applications_for_accessions`
--

CREATE TABLE `applications_for_accessions` (
  `id_application` bigint UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `id_coop` int NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `amount_garages` int NOT NULL,
  `send_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_accepted` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications_for_accessions`
--

INSERT INTO `applications_for_accessions` (`id_application`, `user_id`, `id_coop`, `status`, `amount_garages`, `send_date`, `date_accepted`, `deleted_at`) VALUES
(1, 3, 23, 'pending', 2, '2024-09-09 21:25:05', NULL, NULL),
(2, 2, 4, 'pending', 2, '2024-09-09 21:32:23', NULL, NULL),
(3, 3, 8, 'delete', 3, '2024-09-10 19:57:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applications_garage_to_coop`
--

CREATE TABLE `applications_garage_to_coop` (
  `id_application` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `id_block` bigint DEFAULT NULL,
  `number_garage` bigint NOT NULL,
  `number_block` bigint NOT NULL,
  `number_meter` bigint NOT NULL,
  `id_coop` bigint NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `date_received` datetime DEFAULT NULL,
  `date_accepted` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications_garage_to_coop`
--

INSERT INTO `applications_garage_to_coop` (`id_application`, `user_id`, `id_block`, `number_garage`, `number_block`, `number_meter`, `id_coop`, `status`, `active`, `date_received`, `date_accepted`, `deleted_at`) VALUES
(30, 3, NULL, 4, 1, 365135134, 23, 'pending', 1, '2024-09-09 21:25:05', NULL, NULL),
(31, 3, NULL, 4, 1, 235135135, 23, 'pending', 1, '2024-09-09 21:25:05', NULL, NULL),
(32, 2, NULL, 4, 1, 264235235, 4, 'pending', 1, '2024-09-09 21:32:23', NULL, NULL),
(33, 2, NULL, 4, 1, 12341345135, 4, 'pending', 1, '2024-09-09 21:32:23', NULL, NULL),
(232, 3, NULL, 1, 1, 3513414, 8, 'delete', 1, '2024-09-10 19:57:06', NULL, NULL),
(233, 3, NULL, 1, 1, 14241231, 8, 'delete', 1, '2024-09-10 19:57:06', NULL, NULL),
(234, 3, NULL, 1, 1, 4123123, 8, 'delete', 1, '2024-09-10 19:57:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cooperatives`
--

CREATE TABLE `cooperatives` (
  `id_coop` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_create` datetime NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cooperatives`
--

INSERT INTO `cooperatives` (`id_coop`, `user_id`, `name`, `city`, `address`, `data_create`, `latitude`, `longitude`) VALUES
(3, 1, 'Chairman', 'Междуреченск', 'Кемеровская область, Междуреченск, Октябрьская улица, 15', '2024-01-02 12:42:46', 53.69795608520508, 88.04021930310513),
(4, 3, 'Новый кооператив', 'Междуреченск, микрорайон Притомский', 'Кемеровская область, Междуреченск, микрорайон Притомский', '2024-01-02 15:14:54', 53.6916389465332, 88.03422871244663),
(5, 3, 'Тестик', 'Междуреченск, Горнолыжный комплекс Югус', 'Кемеровская область, Междуреченск, Горнолыжный комплекс Югус', '2024-01-03 19:06:05', 53.66111373901367, 88.08306356403935),
(6, 3, 'Принятая заявка', 'Междуреченск', 'Кемеровская область, Междуреченск', '2024-01-05 20:04:47', 53.708003997802734, 88.06923885457759),
(7, 3, 'Тестик', 'Междуреченск', 'Кемеровская область, Междуреченск', '2024-01-05 20:15:13', 57.12467575073242, 42.289909162109375),
(8, 2, 'Новый 1', 'Междуреченск', 'Кемеровская область, Междуреченск', '2024-01-05 20:13:23', 53.69883728027344, 88.10315258615248),
(9, 2, 'Новый 2', 'Междуреченск', 'Кемеровская область, Междуреченск', '2024-01-05 20:13:43', 53.673377990722656, 88.0337226554977),
(10, 2, 'вввв', 'Москва', 'Москва, 5-й Донской проезд, 21Ас17', '2024-07-04 17:49:34', 55.702537536621094, 37.59567511973109),
(11, 2, 'Новый кооператив', 'Москва', 'Москва, Сосинская улица', '2024-07-04 17:50:30', 55.72934341430664, 37.674188742015026),
(23, 25, 'Дорога', 'Междуреченск', 'Кемеровская область, Междуреченск', '2024-08-22 20:23:36', 53.681502696425, 88.055365213725);

-- --------------------------------------------------------

--
-- Table structure for table `cooperative_blocks`
--

CREATE TABLE `cooperative_blocks` (
  `id_block` bigint UNSIGNED NOT NULL,
  `id_coop` bigint NOT NULL,
  `number_block` bigint NOT NULL,
  `default_kw` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cooperative_blocks`
--

INSERT INTO `cooperative_blocks` (`id_block`, `id_coop`, `number_block`, `default_kw`) VALUES
(1, 4, 1, 4),
(2, 4, 2, 4),
(3, 4, 3, 4),
(4, 4, 4, 22),
(5, 4, 5, 4),
(9, 4, 6, 4),
(10, 4, 7, NULL),
(11, 5, 1, NULL),
(12, 5, 2, NULL),
(13, 5, 3, NULL),
(14, 5, 4, NULL),
(15, 6, 1, NULL),
(16, 6, 2, NULL),
(17, 3, 1, NULL),
(18, 3, 2, NULL),
(19, 3, 3, NULL),
(20, 3, 4, NULL),
(21, 3, 5, NULL),
(22, 4, 8, NULL),
(23, 8, 1, NULL),
(24, 9, 1, NULL),
(25, 9, 2, NULL),
(26, 9, 3, NULL),
(27, 9, 4, NULL),
(69, 23, 1, NULL),
(70, 23, 2, NULL),
(71, 23, 3, NULL),
(72, 23, 4, NULL),
(73, 23, 5, NULL),
(74, 4, 9, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cooperative_blocks_losses_kw`
--

CREATE TABLE `cooperative_blocks_losses_kw` (
  `id_block_losses_kw` bigint UNSIGNED NOT NULL,
  `id_block` bigint NOT NULL,
  `percent_kw` int NOT NULL,
  `date_indication` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cooperative_blocks_losses_kw`
--

INSERT INTO `cooperative_blocks_losses_kw` (`id_block_losses_kw`, `id_block`, `percent_kw`, `date_indication`) VALUES
(1, 1, 2, '2024-02-25 17:32:19'),
(2, 5, 2, '2025-02-04 13:50:42'),
(3, 1, 23, '2023-03-04 13:51:29'),
(4, 1, 4, '2024-03-27 19:58:33'),
(5, 1, 5, '2024-04-04 22:05:16'),
(6, 2, 3, '2024-01-12 18:32:42'),
(7, 1, 5, '2024-01-21 14:19:42'),
(10, 1, 4, '2024-07-21 16:59:40'),
(11, 1, 53, '2023-01-14 14:45:19'),
(12, 1, 3, '2024-06-19 23:16:47'),
(13, 2, 4, '2024-03-25 17:37:00'),
(14, 2, 4, '2024-04-21 21:19:37'),
(15, 3, 4, '2024-04-25 17:37:10'),
(16, 3, 4, '2024-02-21 21:24:30'),
(17, 3, 4, '2024-03-21 21:35:06'),
(18, 4, 22, '2024-06-21 21:35:57'),
(19, 3, 3, '2024-09-25 17:34:07'),
(20, 2, 2, '2024-02-24 21:32:04'),
(21, 2, 1, '2024-05-24 21:32:11'),
(22, 74, 34, '2024-05-24 21:38:37'),
(23, 10, 3, '2024-04-24 21:41:30'),
(24, 22, 4, '2024-06-24 21:42:01'),
(25, 22, 8, '2024-08-24 21:42:21'),
(26, 4, 22, '2024-05-25 17:37:52'),
(27, 4, 1, '2024-04-25 17:39:15'),
(28, 4, 2, '2024-03-25 17:39:19');

-- --------------------------------------------------------

--
-- Table structure for table `coop_losses`
--

CREATE TABLE `coop_losses` (
  `id_losses` bigint UNSIGNED NOT NULL,
  `id_coop` int NOT NULL,
  `losses_value` int NOT NULL,
  `date_indication` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coop_losses`
--

INSERT INTO `coop_losses` (`id_losses`, `id_coop`, `losses_value`, `date_indication`) VALUES
(1, 4, 4, '2024-01-19'),
(2, 4, 2, '2024-02-19'),
(3, 4, 5, '2024-03-19'),
(4, 4, 3, '2024-05-19'),
(5, 4, 6, '2024-06-19'),
(6, 4, 6, '2024-07-19'),
(7, 4, 4, '2023-01-19'),
(8, 4, 4, '2024-04-27'),
(9, 4, 4, '2024-10-30'),
(10, 4, 3, '2024-08-06'),
(11, 4, 2, '2024-09-06'),
(12, 4, 4, '2024-11-06'),
(13, 4, 5, '2024-12-06');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id_fee` bigint UNSIGNED NOT NULL,
  `id_coop` bigint NOT NULL,
  `type_payment` bigint NOT NULL,
  `value` bigint NOT NULL,
  `date_indication` datetime NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id_fee`, `id_coop`, `type_payment`, `value`, `date_indication`, `deleted_at`) VALUES
(1, 4, 3, 50, '2024-11-04 20:30:58', NULL),
(2, 4, 4, 50, '2024-11-04 20:40:26', NULL),
(3, 4, 3, 55, '2024-07-04 20:42:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `garages`
--

CREATE TABLE `garages` (
  `id_garage` bigint UNSIGNED NOT NULL,
  `id_application` bigint NOT NULL,
  `id_block` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `number_garage` bigint NOT NULL,
  `number_block` bigint NOT NULL,
  `id_meter_number` bigint DEFAULT NULL,
  `id_coop` bigint NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `garages`
--

INSERT INTO `garages` (`id_garage`, `id_application`, `id_block`, `user_id`, `number_garage`, `number_block`, `id_meter_number`, `id_coop`, `deleted_at`) VALUES
(1, 1, 1, 34, 1, 1, 1, 4, NULL),
(2, 2, 1, 34, 2, 1, 2, 4, NULL),
(3, 3, 2, 34, 1, 2, 3, 4, NULL),
(4, 4, 1, 7, 3, 1, 4, 4, NULL),
(5, 5, 3, 8, 1, 3, 5, 4, NULL),
(6, 6, 3, 26, 2, 3, 6, 4, NULL),
(7, 7, 1, 26, 4, 1, 7, 4, NULL),
(8, 8, 1, 27, 5, 1, 8, 4, NULL),
(9, 9, 1, 27, 6, 1, 9, 4, NULL),
(10, 10, 1, 27, 7, 1, 10, 4, NULL),
(11, 11, 2, 28, 2, 2, 11, 4, NULL),
(12, 12, 13, 28, 43, 3, 12, 5, NULL),
(13, 14, 2, 25, 43, 2, 13, 4, NULL),
(14, 16, 5, 33, 4, 5, 14, 4, '2024-09-06 00:36:36'),
(18, 32, 9, 26, 3, 6, 15, 4, '2024-09-06 20:53:22'),
(19, 18, 4, 35, 3, 2, 16, 4, NULL),
(20, 28, 70, 3, 6, 2, 17, 23, '2024-09-06 01:44:43'),
(21, 31, 72, 3, 3, 4, 18, 23, '2024-09-06 01:44:43'),
(22, 27, 70, 3, 5, 2, 19, 23, '2024-09-06 01:44:43'),
(23, 15, 2, 30, 54, 2, 20, 4, NULL),
(24, 29, 23, 3, 42, 1, 21, 8, NULL),
(25, 30, 23, 3, 3, 1, 22, 8, NULL),
(26, 33, 23, 26, 4, 1, 23, 8, NULL),
(27, 34, 11, 26, 5, 1, 24, 5, '2024-09-07 13:19:39'),
(28, 35, 11, 26, 5, 1, 25, 5, '2024-09-07 13:19:39'),
(29, 36, 11, 26, 51, 1, 26, 5, '2024-09-07 13:19:39'),
(30, 32, 1, 2, 4, 1, 30, 4, '2024-09-20 21:00:16'),
(31, 33, 1, 2, 4, 1, 31, 4, '2024-09-20 21:00:16');

-- --------------------------------------------------------

--
-- Table structure for table `meter_numbers_blocks`
--

CREATE TABLE `meter_numbers_blocks` (
  `id_meter_number` bigint UNSIGNED NOT NULL,
  `id_block` bigint NOT NULL,
  `meter_number` bigint NOT NULL,
  `initially_kw` bigint NOT NULL,
  `active` bigint NOT NULL,
  `creation_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meter_numbers_blocks`
--

INSERT INTO `meter_numbers_blocks` (`id_meter_number`, `id_block`, `meter_number`, `initially_kw`, `active`, `creation_date`) VALUES
(1, 1, 352351234, 1800, 0, '2024-10-08'),
(3, 1, 453452354234, 24444, 0, '2024-10-09'),
(4, 1, 23414123, 3333, 0, '2024-10-09'),
(5, 2, 53234234, 4444, 0, '2024-10-09'),
(6, 2, 35234234, 444, 1, '2024-10-09'),
(7, 1, 546345345, 455, 1, '2024-10-09');

-- --------------------------------------------------------

--
-- Table structure for table `meter_numbers_coops`
--

CREATE TABLE `meter_numbers_coops` (
  `id_meter_number` bigint UNSIGNED NOT NULL,
  `id_coop` bigint NOT NULL,
  `meter_number` bigint NOT NULL,
  `initially_kw` bigint NOT NULL,
  `active` bigint NOT NULL,
  `creation_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meter_numbers_coops`
--

INSERT INTO `meter_numbers_coops` (`id_meter_number`, `id_coop`, `meter_number`, `initially_kw`, `active`, `creation_date`) VALUES
(1, 4, 531513412429, 2000, 1, '2024-09-21');

-- --------------------------------------------------------

--
-- Table structure for table `meter_numbers_garages`
--

CREATE TABLE `meter_numbers_garages` (
  `id_meter_number` bigint UNSIGNED NOT NULL,
  `id_garage` bigint NOT NULL,
  `meter_number` bigint NOT NULL,
  `active` bigint NOT NULL,
  `creation_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meter_numbers_garages`
--

INSERT INTO `meter_numbers_garages` (`id_meter_number`, `id_garage`, `meter_number`, `active`, `creation_date`) VALUES
(1, 1, 532523623623, 1, '2024-09-09'),
(2, 2, 5353425234, 1, '2024-09-09'),
(3, 3, 3523452353, 1, '2024-09-09'),
(4, 4, 35236234, 1, '2024-09-09'),
(5, 5, 46243224, 1, '2024-09-09'),
(6, 6, 52343565, 1, '2024-09-09'),
(7, 7, 53462356324, 1, '2024-09-09'),
(8, 8, 5236356234324, 1, '2024-09-09'),
(9, 9, 3523514, 1, '2024-09-09'),
(10, 10, 5346362344, 1, '2024-09-09'),
(11, 11, 3523613534, 1, '2024-09-09'),
(12, 12, 3467235235, 1, '2024-09-09'),
(13, 13, 461235234, 1, '2024-09-09'),
(14, 14, 4725234234, 1, '2024-09-09'),
(15, 18, 9999999999, 1, '2024-09-09'),
(16, 19, 1234124, 1, '2024-09-09'),
(17, 20, 4362467276, 1, '2024-09-09'),
(18, 21, 234526236, 1, '2024-09-09'),
(19, 22, 26323535, 1, '2024-09-09'),
(20, 23, 47243672435, 1, '2024-09-09'),
(21, 24, 535236123546, 1, '2024-09-09'),
(22, 25, 5235235, 0, '2024-09-09'),
(23, 26, 3567246216, 1, '2024-09-09'),
(24, 27, 3461235135, 1, '2024-09-09'),
(25, 28, 123415315, 1, '2024-09-09'),
(26, 29, 345235135, 1, '2024-09-09'),
(27, 25, 31234123, 0, '2024-09-09'),
(28, 25, 2135135355, 0, '2024-09-09'),
(29, 25, 35124, 1, '2024-09-09'),
(30, 30, 264235235, 1, '2024-09-10'),
(31, 31, 12341345135, 1, '2024-09-10');

-- --------------------------------------------------------

--
-- Table structure for table `meter_readings_blocks`
--

CREATE TABLE `meter_readings_blocks` (
  `id_reading` bigint UNSIGNED NOT NULL,
  `id_block` bigint NOT NULL,
  `id_coop` bigint NOT NULL,
  `id_meter_number_block` bigint NOT NULL,
  `kw_meter` bigint NOT NULL,
  `img_meter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `save_day` datetime NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meter_readings_blocks`
--

INSERT INTO `meter_readings_blocks` (`id_reading`, `id_block`, `id_coop`, `id_meter_number_block`, `kw_meter`, `img_meter`, `save_day`, `deleted_at`) VALUES
(1, 1, 4, 1, 333, NULL, '2024-10-09 00:52:31', NULL),
(2, 1, 4, 3, 3423, NULL, '2024-10-09 00:55:54', NULL),
(3, 1, 4, 4, 444, 'фото_счётчика_2024-10-09_01-47-51.jpg', '2024-10-09 01:47:51', NULL),
(4, 1, 4, 4, 222222, NULL, '2024-09-09 01:01:19', NULL),
(5, 2, 4, 5, 2342344, NULL, '2024-10-09 01:12:57', NULL),
(6, 1, 4, 7, 4445, 'фото_счётчика_2024-10-15_20-26-01.jpg', '2024-10-27 19:58:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `meter_readings_coops`
--

CREATE TABLE `meter_readings_coops` (
  `id_reading` bigint UNSIGNED NOT NULL,
  `id_coop` bigint NOT NULL,
  `id_meter_number_coop` bigint NOT NULL,
  `kw_meter` bigint NOT NULL,
  `img_meter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `save_day` datetime NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meter_readings_coops`
--

INSERT INTO `meter_readings_coops` (`id_reading`, `id_coop`, `id_meter_number_coop`, `kw_meter`, `img_meter`, `save_day`, `deleted_at`) VALUES
(1, 4, 1, 12235, 'фото_счётчика_2024-10-22_23-00-43.jpg', '2024-10-27 00:54:41', NULL),
(2, 4, 1, 43433, NULL, '2023-10-26 23:28:42', NULL),
(3, 4, 1, 34, NULL, '2025-10-26 23:53:27', NULL),
(4, 4, 1, 3423, NULL, '2025-05-09 23:56:33', NULL),
(5, 4, 1, 234233, NULL, '2026-05-09 23:57:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `meter_readings_users`
--

CREATE TABLE `meter_readings_users` (
  `id_reading` bigint UNSIGNED NOT NULL,
  `id_garage` bigint NOT NULL,
  `id_block` bigint NOT NULL,
  `id_coop` bigint NOT NULL,
  `id_meter_number_garage` bigint NOT NULL,
  `kw_meter` bigint NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_meter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_hash` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meter_readings_users`
--

INSERT INTO `meter_readings_users` (`id_reading`, `id_garage`, `id_block`, `id_coop`, `id_meter_number_garage`, `kw_meter`, `status`, `img_meter`, `image_hash`, `send_date`) VALUES
(1, 24, 23, 8, 21, 3234, 'accepted', 'Михаил Шевяков Дмитревич_2024-08-12_14-39-39.jpg', 'eea1d4af66b08b380b1a839165120280ca39ee2288e05ea59047b4691da71120', '2024-07-12 14:39:39'),
(2, 24, 23, 8, 21, 342, 'pending', 'Михаил Шевяков Дмитревич_2024-09-12_14-40-24.jpg', '3e8f9b54977744f74e5f87b53a48d9d480be575cadc017a73f4357aecc2f5ccd', '2024-08-12 14:40:24'),
(3, 24, 23, 8, 21, 23423, 'pending', 'Михаил Шевяков Дмитревич_2024-09-12_14-41-10.jpg', '1f099e318e6fe6a558f7306ad6d3b8df8ed058bc4f89f381ee84faa7bb1c67e4', '2024-09-12 14:41:10'),
(4, 25, 23, 8, 29, 231, 'accepted', 'Михаил Шевяков Дмитревич_2024-09-12_15-20-43.jpg', 'c813d4eb953f4eae5eee964f85e177af492781589047a231c1ed4e96ac0e2b0e', '2024-07-12 15:20:45'),
(5, 25, 23, 8, 29, 123, 'pending', 'Михаил Шевяков Дмитревич_2024-09-12_15-22-42.png', '3be2c1b1e53bef652389ed20c527b3d08d7d88784583df13d53cf05b3329ad35', '2024-08-12 15:22:43'),
(6, 25, 23, 8, 29, 123, 'pending', 'Михаил Шевяков Дмитревич_2024-09-12_15-22-58.png', '1a03f1726b76b9a47373235c3182b3860f4a2602695191f830ecf17b34b6a931', '2024-09-12 15:22:59'),
(7, 4, 1, 4, 4, 151, 'pending', 'Алексей Георгевич Кабачков_2024-09-18_15-08-42.jpg', 'eea1d4af66b08b380b1a839165120280ca39ee2288e05ea59047b4691da71120', '2024-09-18 15:08:44'),
(9, 11, 2, 4, 11, 2312, 'pending', 'Друвой Артём Сергеевич_2024-09-21_21-07-06.jpg', 'eea1d4af66b08b380b1a839165120280ca39ee2288e05ea59047b4691da71120', '2024-09-21 21:07:06'),
(10, 24, 23, 8, 21, 34234234, 'pending', 'Михаил Шевяков Дмитревич_2024-10-09_01-25-42.jpg', 'eea1d4af66b08b380b1a839165120280ca39ee2288e05ea59047b4691da71120', '2024-10-09 01:25:42');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_resets_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2023_10_12_201948_create_sessions_table', 1),
(4, '2023_10_12_202635_create_users_table', 1),
(5, '2023_10_20_190550_create_cooperative_table', 1),
(8, '2023_11_03_214230_create_admin_users_table', 1),
(9, '2023_11_04_191949_create_admin_sessions_table', 1),
(10, '2023_11_22_211342_create__applications__for__accessions', 1),
(11, '2023_11_26_165050_create_user_and_coop_table', 1),
(13, '2023_11_03_204246_create_applications_create_new_coops_table', 2),
(19, '2024_01_10_152505_create_cooperative_blocks_losses_kw_table', 5),
(20, '2023_11_22_211342_create_applications_for_accessions', 6),
(24, '2024_01_10_151716_create_cooperative_blocks_table', 7),
(26, '2024_01_08_193442_create_meters_readings_table', 8),
(27, '2023_11_01_211749_create_garages_table', 9),
(29, '2023_12_10_124822_create_applications_garage_to_coop_table', 10),
(30, '2024_03_09_183345_create_rates_table', 11),
(31, '2024_03_16_201726_create_payments_table', 12),
(33, '2024_05_19_224010_create_coop_losses_table', 13),
(35, '2024_09_09_120412_create_meter_numbers_garages_table', 14),
(36, '2024_09_13_174755_create_meter_numbers_coops_table', 15),
(38, '2024_09_25_162432_create_meter_readings_blocks_table', 16),
(39, '2024_10_05_213239_create_meter_numbers_blocks_table', 17),
(40, '2024_10_15_224042_create_meter_readings_coops_table', 18),
(42, '2024_10_27_214043_create_user_balances_table', 19),
(43, '2024_10_29_223037_create_fees_table', 20),
(44, '2024_11_04_224100_create_total_paid_for_electricity_table', 21);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id_payment` bigint UNSIGNED NOT NULL,
  `id_coop` int NOT NULL,
  `id_garage` bigint NOT NULL,
  `payment_value` int DEFAULT NULL,
  `type_payment` int DEFAULT '0',
  `date_indication` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id_payment`, `id_coop`, `id_garage`, `payment_value`, `type_payment`, `date_indication`) VALUES
(1, 4, 4, 444, 0, '2024-11-01'),
(2, 4, 3, 0, 0, '2024-11-01'),
(3, 4, 4, 0, 0, '2024-10-01'),
(4, 4, 4, 66, 0, '2024-03-01');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `id_rate` bigint UNSIGNED NOT NULL,
  `id_coop` bigint NOT NULL,
  `tariff_value` float NOT NULL,
  `date_indication` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rates`
--

INSERT INTO `rates` (`id_rate`, `id_coop`, `tariff_value`, `date_indication`) VALUES
(1, 4, 11.2, '2024-03-13 16:11:20'),
(2, 4, 231, '2024-06-13 16:11:36'),
(3, 4, 12.3, '2024-11-13 16:12:28'),
(4, 4, 23, '2444-04-13 16:12:54'),
(5, 4, 34, '2025-04-13 16:16:27'),
(6, 4, 234, '2025-01-13 16:23:29'),
(7, 4, 42, '2036-01-13 19:54:38'),
(8, 4, 56, '2024-01-13 19:58:57'),
(9, 4, 3, '2023-03-13 20:50:54'),
(10, 4, 22, '2024-05-27 00:54:51'),
(11, 4, 12.6, '2024-10-30 23:31:18'),
(12, 4, 5, '2024-02-06 22:57:32'),
(13, 4, 5, '2024-04-06 22:57:34'),
(14, 4, 6, '2024-07-06 22:57:35'),
(15, 4, 7, '2024-08-06 22:57:38'),
(16, 4, 8, '2024-09-06 22:57:39'),
(17, 4, 3, '2024-12-06 22:57:42');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('jUdj0uPZE4EVSuS10i5Iy6naBNJQBQ8Ic5grFtOR', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 OPR/114.0.0.0', 'ZXlKcGRpSTZJbXQyU0ZkYWJqQTJkSEJrWmxWR1RFbE9OM295UjJjOVBTSXNJblpoYkhWbElqb2laVlJTWldKU1FuaG1TWEJUY1RORFUxbzFha3MyYmtWclQzZDZWRTVOVkZNMGNtZDRUV0poVm1SNk1HeEVTSEJHUTIwekt6QXpOU3RCWjFsT2JqZFFMekk1ZFRsUlIzY3lNMnBTUjI5SVp6SndlRkl6ZFVGcVVFTm1Ubmh5VkRKVmIweHZWREYzUkRseFoxSlFWa0ZrYmxoTFNTOVlVakJ1U0RsTk1GRmtWWGsxY1dWRmFHUk5PV0p1YVU1aFozVlFabHBDVUV3MVdtaFZRbEozUTBGWVJtcHBVblJ1YlUxd1RucFhORTVPUkdGWlRFazVNamxTY0UxblZVWXZObHB1UjNweGFISk1RVFJXTDJoMVZuVklXVGszVGxCTFZVZFRWWHBQUW5aTmRGcFRka1UzV0hJd05URjRNelpIZFZBM2NEaG9jSEI0VEZCWVVVbFVSR3hPYVRaelNuQXdlakVySzNSR01IbGFWbEJ2ZGtoMWVEaHNibUZ4TWtFNGQwVk1MMEZZUjBGSmRXVnFXWFJUWTFoQ00zQkJZazUyTVhOUlNFeGpZM0ZaTUdOQlZVOXdVMVp1VldSUFIyMW9OakpCWTFablYzUk5iSGt2YjJOamJYZHZlakJaV0ZCRllsTkVTazUwTURZeFZuVjZSRzFUVVdsaE1HdzNkMWhrVmxOaElpd2liV0ZqSWpvaU1UYzJOVE0xTlRRM09EUmlaRGd3TWpRM09UWXhaakJsTmpFeFlUaGxPR0UzWkdNd1pqZ3lZV0ZrTURSalpUVmxZamM0TW1Sa05HWmhOVEl3Tm1GbE9DSXNJblJoWnlJNklpSjk=', '1730911815'),
('WLYOFgzMNUAE1FDipeDkMB6DziMGGJKNsGZ8IuuH', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 OPR/114.0.0.0', 'ZXlKcGRpSTZJbmx4VXpoM2FYaDJaMDFMVHpad1EwZHFNRzV4Y1ZFOVBTSXNJblpoYkhWbElqb2lOSFJtWjBkU09WZHpNMDVVWWtOUlV6SjZjbmhVU2tOU2NEQlpVMVYwT1RkSFVrMVBVVEpMVFVsU1lsWlJRa1JHVUhsUldVY3hNbWczVm1oQ2JpdDZNVUUzTkcxV1IwTmhkekJUZDNSRVRESk5NMkpVUVZRdlJqa3JVMU5KT1Zrd1EwTlVZMk5CU2t4YVNYUjNZM1ZRWm5Jckt6bDBaMEUyVURGM1IyNUxRV0pGVkhKek9EWkpaV2hTYVVkNWJERTJSVkkyZG00NVEzbENVbUkzY1VaeVpUbHZjMjlMY2tob1FrVTFNM0pqZVhkYVJWQndNVlpDTlRWaFEyNTFiMVZSTkVSaFZtWmlaVk42TVdSQlNDdDRZVWg1UTJWd1QyOUtiRVJpTTJrMk56SmpibXhuUjFscmNIZHFjbTkxU3pjd01sQTVVVVk1WldJMlVXcHJXVWQwTWtwS0wybFRibE50ZDFGb1YzTmFSMkZyYVhsNVRHRTVabFIxZGk5d1owdEpNRzg0VmtSSmVXcDFXa3BzUW1aTk5ISTJObWxQUjNKRVVXazJUR3BVYm5OUE5qQk9hMjlNV1VNdlowcHZjVk5tTkdKbGFFeElaRzh2TjJaV01IQjBRa1JwVVRSTEszUllTRTVtTHpodmVIVnhUVEJtVkVNeVlrSXhSQ3QzYm5KRElpd2liV0ZqSWpvaVptWTRObVF3TURCallqWTBOREU0WkRreVpqRXpORGczTUdWa00yTTFPRGMwWldNek5UVmlOR1EyTlRrMlpqa3hZbVUxTmpaaFpqVXdPV1EzWXpFMll5SXNJblJoWnlJNklpSjk=', '1746809807');

-- --------------------------------------------------------

--
-- Table structure for table `total_paid_for_electricity`
--

CREATE TABLE `total_paid_for_electricity` (
  `id_total_elec` bigint UNSIGNED NOT NULL,
  `id_coop` bigint NOT NULL,
  `value` bigint NOT NULL,
  `date_indication` datetime NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `total_paid_for_electricity`
--

INSERT INTO `total_paid_for_electricity` (`id_total_elec`, `id_coop`, `value`, `date_indication`, `deleted_at`) VALUES
(1, 4, 444, '2024-11-01 00:00:00', NULL),
(2, 4, 0, '2024-10-01 00:00:00', NULL),
(3, 4, 66, '2024-03-01 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `fio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` bigint NOT NULL,
  `second_phone` bigint DEFAULT NULL,
  `home_phone` bigint DEFAULT NULL,
  `email` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `region` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_reg` datetime DEFAULT NULL,
  `id_al` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fio`, `phone`, `second_phone`, `home_phone`, `email`, `password`, `region`, `data_reg`, `id_al`) VALUES
(1, 'Панкин Игорь Сергеевич', 79236227112, NULL, NULL, 'Pankin.2004@mail.ru', '$2y$10$rpzr2UGzExdlxRQgNxEoIe/J2P6cMPkG1BBQqa1TQixoFndTVHvHW', 'Иркутская область', '2023-12-30 14:01:35', 2),
(2, 'Герман Агич Александрович', 79234534252, NULL, NULL, 'Agich2004@mail.ru', '$2y$10$aBuS1e9lrF9IMR7z/o2mq../VQnHZeR9XbO7OcYZt4g.8Oil2pLty', 'Ивановская область', '2024-01-02 12:44:46', 2),
(3, 'Михаил Шевяков Дмитревич', 79236227113, 75236236234, 534222, 'misha20233@mail.ru', '$2y$10$5M.5MMcxMMa46Rfg47wY.eq.hyHiQR6og.3VT4uJG8lQecAzuqXY6', 'Кемеровская область', '2024-01-02 12:45:23', 2),
(4, 'Новый пользователь Онлайн', 75234235134, NULL, NULL, 'darvold@mail.ru', '$2y$10$dEEUT/1dx52cB2JkEltIz.myAtcrqjaJKMxEoK/yO/17N8So7AfdW', 'Еврейская автономная область', '2024-01-12 15:27:03', 1),
(7, 'Алексей Георгевич Кабачков', 74534534643, NULL, NULL, 'farget@mail.ru', '$2y$10$Cedq1L6586gKH6lupJEeXu7ReYwyh4QaaDwsDr8a4/WK3emDUcGuC', 'Забайкальский край', '2024-01-15 19:42:41', 2),
(8, 'Новый пользователь Онлайн', 75423543532, NULL, NULL, 'Asd@mail.ru', '$2y$10$ZpLgXpLUSPE2R2f9AJ4tBOpULVIq9JeDwCptiJzD8E/1kKL.JE99.', 'Кабардино-Балкарская Республика', '2024-01-16 19:41:11', 2),
(25, 'Табатчиков Иван Владимирович', 76456457234, NULL, NULL, 'Tqabas@mail.ru', '$2y$10$PEbUtXN5kfN0H/PlqtpBneYQGQ4M1cV78/dYWpN8GdTyGuo8R9ph2', 'Ивановская область', '2024-05-20 23:09:00', 2),
(26, 'Шимиль Дмитрий Олегович', 74673462123, NULL, NULL, 'Fass23@mail.ru', '$2y$10$QPF6rU9lgrDD.r2zYFuRKOnPJYWozg6gm75FdTuXe8lIhKz2yQovG', 'Еврейская автономная область', '2024-05-20 23:09:26', 2),
(27, 'Панкин Олег Сергеевич', 73523513546, NULL, NULL, 'Olet@mail.ru', '$2y$10$GUxR/zoSO9xes2QA/m8m9uSsQzQarsgHLQKsJybLqdnammSv/YZvi', 'Забайкальский край', '2024-05-20 23:11:15', 2),
(28, 'Друвой Артём Сергеевич', 75346724362, NULL, NULL, 'Dassd2@mail.ru', '$2y$10$2n7TSqKkeXDn18kJODShM.Fp5PvgBme0jDb6Gjxp08rJo6ZNfHuXm', 'Ивановская область', '2024-05-20 23:11:52', 2),
(30, 'Панкин Игорь Сергеевич', 79236227111, NULL, NULL, 'misha20233@mail.ru', '$2y$10$ZEsrn4ueMzEkZ1lV2QZZEuJnba/A9eAN0fKwJ3ehZRyaojvEXIAGS', 'Кемеровская область', '2024-07-24 18:35:39', 2),
(33, 'Панкин Игорь Сергеевич 2', 72342353523, NULL, NULL, 'misha20233@mail.ru', '$2y$10$/aCDt4FYLmyk285c/knInOBsiGvC6ebSQ5B9o/pjYs./Dohy9zRNK', 'Кемеровская область', '2024-07-24 19:14:31', 2),
(34, 'Георгий Алексей Дмитриевич', 72342351234, 75213423412, 324212, 'Geor@mail.ru', '$2y$10$7n0t/pJHZYraeGsEZbpRmuooH2zVp3HNBbI6Kcjau.7ISQEPCpOnS', 'Кемеровская область', '2024-07-26 17:18:02', 2),
(35, 'Панкин Сергей Валентинович', 73243512324, NULL, NULL, 'Agich22004@mail.ru', '$2y$10$3MlpDL8FlHIg/CW.43sZKeb1FHwvfRYm4uIwBTtVksp8JMYezYvai', 'Кемеровская область', '2024-08-14 15:10:58', 2),
(36, 'Йоров Алейсей Паторович', 73523613563, NULL, NULL, 'Agich2041404@mail.ru', '$2y$10$iLQYPkLvAYydmHPtbEOigumx59fuMLXERT.VVsp.IIXBw1FeTVWNu', 'Кемеровская область', '2024-09-04 17:58:45', 2);

-- --------------------------------------------------------

--
-- Table structure for table `user_and_coop`
--

CREATE TABLE `user_and_coop` (
  `id_connection` bigint UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `id_coop` int NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_and_coop`
--

INSERT INTO `user_and_coop` (`id_connection`, `user_id`, `id_coop`, `deleted_at`) VALUES
(1, 34, 4, NULL),
(2, 7, 4, NULL),
(3, 8, 4, NULL),
(4, 26, 4, NULL),
(5, 27, 4, NULL),
(6, 28, 4, NULL),
(7, 28, 5, NULL),
(8, 25, 4, NULL),
(9, 35, 4, NULL),
(10, 33, 4, '2024-09-06 00:36:36'),
(11, 3, 23, '2024-09-06 01:44:43'),
(12, 30, 4, NULL),
(13, 26, 6, '2024-09-06 21:07:47'),
(14, 3, 8, NULL),
(15, 26, 8, NULL),
(16, 26, 5, '2024-09-07 13:19:39'),
(17, 2, 4, '2024-09-20 21:00:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_balances`
--

CREATE TABLE `user_balances` (
  `id_balance` bigint UNSIGNED NOT NULL,
  `id_garage` bigint NOT NULL,
  `balance` bigint NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_balances`
--

INSERT INTO `user_balances` (`id_balance`, `id_garage`, `balance`, `deleted_at`) VALUES
(1, 4, 100, NULL),
(2, 3, 0, NULL),
(3, 2, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_sessions_user_id_index` (`user_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications_create_new_coops`
--
ALTER TABLE `applications_create_new_coops`
  ADD PRIMARY KEY (`id_application`);

--
-- Indexes for table `applications_for_accessions`
--
ALTER TABLE `applications_for_accessions`
  ADD PRIMARY KEY (`id_application`);

--
-- Indexes for table `applications_garage_to_coop`
--
ALTER TABLE `applications_garage_to_coop`
  ADD PRIMARY KEY (`id_application`);

--
-- Indexes for table `cooperatives`
--
ALTER TABLE `cooperatives`
  ADD PRIMARY KEY (`id_coop`);

--
-- Indexes for table `cooperative_blocks`
--
ALTER TABLE `cooperative_blocks`
  ADD PRIMARY KEY (`id_block`);

--
-- Indexes for table `cooperative_blocks_losses_kw`
--
ALTER TABLE `cooperative_blocks_losses_kw`
  ADD PRIMARY KEY (`id_block_losses_kw`);

--
-- Indexes for table `coop_losses`
--
ALTER TABLE `coop_losses`
  ADD PRIMARY KEY (`id_losses`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id_fee`);

--
-- Indexes for table `garages`
--
ALTER TABLE `garages`
  ADD PRIMARY KEY (`id_garage`);

--
-- Indexes for table `meter_numbers_blocks`
--
ALTER TABLE `meter_numbers_blocks`
  ADD PRIMARY KEY (`id_meter_number`);

--
-- Indexes for table `meter_numbers_coops`
--
ALTER TABLE `meter_numbers_coops`
  ADD PRIMARY KEY (`id_meter_number`);

--
-- Indexes for table `meter_numbers_garages`
--
ALTER TABLE `meter_numbers_garages`
  ADD PRIMARY KEY (`id_meter_number`);

--
-- Indexes for table `meter_readings_blocks`
--
ALTER TABLE `meter_readings_blocks`
  ADD PRIMARY KEY (`id_reading`);

--
-- Indexes for table `meter_readings_coops`
--
ALTER TABLE `meter_readings_coops`
  ADD PRIMARY KEY (`id_reading`);

--
-- Indexes for table `meter_readings_users`
--
ALTER TABLE `meter_readings_users`
  ADD PRIMARY KEY (`id_reading`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id_payment`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `rates`
--
ALTER TABLE `rates`
  ADD PRIMARY KEY (`id_rate`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`);

--
-- Indexes for table `total_paid_for_electricity`
--
ALTER TABLE `total_paid_for_electricity`
  ADD PRIMARY KEY (`id_total_elec`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_unique` (`phone`) USING BTREE;

--
-- Indexes for table `user_and_coop`
--
ALTER TABLE `user_and_coop`
  ADD PRIMARY KEY (`id_connection`);

--
-- Indexes for table `user_balances`
--
ALTER TABLE `user_balances`
  ADD PRIMARY KEY (`id_balance`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `applications_create_new_coops`
--
ALTER TABLE `applications_create_new_coops`
  MODIFY `id_application` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `applications_for_accessions`
--
ALTER TABLE `applications_for_accessions`
  MODIFY `id_application` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `applications_garage_to_coop`
--
ALTER TABLE `applications_garage_to_coop`
  MODIFY `id_application` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;

--
-- AUTO_INCREMENT for table `cooperatives`
--
ALTER TABLE `cooperatives`
  MODIFY `id_coop` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `cooperative_blocks`
--
ALTER TABLE `cooperative_blocks`
  MODIFY `id_block` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `cooperative_blocks_losses_kw`
--
ALTER TABLE `cooperative_blocks_losses_kw`
  MODIFY `id_block_losses_kw` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `coop_losses`
--
ALTER TABLE `coop_losses`
  MODIFY `id_losses` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id_fee` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `garages`
--
ALTER TABLE `garages`
  MODIFY `id_garage` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `meter_numbers_blocks`
--
ALTER TABLE `meter_numbers_blocks`
  MODIFY `id_meter_number` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `meter_numbers_coops`
--
ALTER TABLE `meter_numbers_coops`
  MODIFY `id_meter_number` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `meter_numbers_garages`
--
ALTER TABLE `meter_numbers_garages`
  MODIFY `id_meter_number` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `meter_readings_blocks`
--
ALTER TABLE `meter_readings_blocks`
  MODIFY `id_reading` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `meter_readings_coops`
--
ALTER TABLE `meter_readings_coops`
  MODIFY `id_reading` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `meter_readings_users`
--
ALTER TABLE `meter_readings_users`
  MODIFY `id_reading` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id_payment` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rates`
--
ALTER TABLE `rates`
  MODIFY `id_rate` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `total_paid_for_electricity`
--
ALTER TABLE `total_paid_for_electricity`
  MODIFY `id_total_elec` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user_and_coop`
--
ALTER TABLE `user_and_coop`
  MODIFY `id_connection` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_balances`
--
ALTER TABLE `user_balances`
  MODIFY `id_balance` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
