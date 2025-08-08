-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2025 at 11:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cemo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `request_id` varchar(20) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `notification_type`, `message`, `client_id`, `request_id`, `is_read`, `created_at`) VALUES
(1, 'new_request', 'New service request from test test for Grass-Cutting on 2025-08-07', 9, 'REQ202508056621', 1, '2025-08-05 19:48:53'),
(2, 'new_request', 'New service request from test test for Garbage Collection on 2025-08-15', 9, 'REQ202508057147', 1, '2025-08-05 20:14:57'),
(3, 'new_request', 'New service request from test test for Grass-Cutting on 2025-08-09', 9, 'REQ202508051880', 1, '2025-08-05 20:32:21'),
(4, 'new_request', 'New service request from test test for Street Cleaning on 2025-08-06', 9, 'REQ202508055999', 1, '2025-08-05 20:46:47'),
(5, 'new_request', 'New service request from test test for Grass-Cutting on 2025-08-06', 9, 'REQ202508054123', 1, '2025-08-05 20:51:23'),
(6, 'new_request', 'New service request from test test for Garbage Collection on 2025-08-07 at 08:00', 9, 'REQ202508052062', 0, '2025-08-05 21:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `admin_table`
--

CREATE TABLE `admin_table` (
  `admin_id` int(11) NOT NULL,
  `user_role` varchar(225) NOT NULL,
  `first_name` varchar(225) NOT NULL,
  `last_name` varchar(225) NOT NULL,
  `email` varchar(225) NOT NULL,
  `gender` varchar(225) NOT NULL,
  `address` varchar(225) NOT NULL,
  `birth_date` date NOT NULL,
  `contact` varchar(225) NOT NULL,
  `reset_token` varchar(225) NOT NULL,
  `password` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_table`
--

INSERT INTO `admin_table` (`admin_id`, `user_role`, `first_name`, `last_name`, `email`, `gender`, `address`, `birth_date`, `contact`, `reset_token`, `password`) VALUES
(1, 'Admin', 'Jack', 'Da Great', 'jack@gmail.com', 'Male', 'Pacol', '2003-09-27', '09123456789', '', '$2y$10$srxMYQBpVeF5BpuTRAjNyOD6B8MMeBLzLdVAP0icOpphxpMsN0zLK'),
(3, 'Staff', 'Jack', 'Narvaez', 'jake@gmail.com', '', 'Napoles', '0000-00-00', '00000000000', '', '$2y$10$F5J.3u8WH8xgw/CweBzYQuRpAPK9s8Jv/D/brSEUBX5FxnQFKB5.q');

-- --------------------------------------------------------

--
-- Table structure for table `barangays_table`
--

CREATE TABLE `barangays_table` (
  `brgy_id` int(24) NOT NULL,
  `barangay` varchar(225) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `city` varchar(225) NOT NULL,
  `facebook_link` varchar(225) NOT NULL,
  `link_text` varchar(225) NOT NULL,
  `schedule_id` int(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangays_table`
--

INSERT INTO `barangays_table` (`brgy_id`, `barangay`, `latitude`, `longitude`, `city`, `facebook_link`, `link_text`, `schedule_id`) VALUES
(1, 'Abuanan', 10.525313, 122.992415, 'Bago City', 'https://www.google.com/', 'test', 0),
(2, 'Alianza', 10.47393, 122.92993, 'Bago City', '', '', 0),
(3, 'Atipuluan', 10.51083, 122.95626, 'Bago City', '', '', 0),
(4, 'Bacong-Montilla', 10.51895, 123.03452, 'Bago City', '', '', 0),
(5, 'Bagroy', 10.47718, 122.87212, 'Bago City', '', '', 0),
(6, 'Balingasag', 10.53161, 122.84595, 'Bago City', '', '', 0),
(7, 'Binubuhan', 10.45755, 123.00718, 'Bago City', '', '', 0),
(8, 'Busay', 10.53718, 122.88822, 'Bago City', '', '', 0),
(9, 'Calumangan', 10.56009, 122.8768, 'Bago City', '', '', 0),
(10, 'Caridad', 10.48198, 122.90567, 'Bago City', '', '', 0),
(11, 'Don Jorge L. Araneta', 10.47642, 122.94615, 'Bago City', '', '', 0),
(12, 'Dulao', 10.54916, 122.95165, 'Bago City', '', '', 0),
(13, 'Ilijan', 10.453, 123.05486, 'Bago City', '', '', 0),
(14, 'Lag-Asan', 10.53006, 122.838575, 'Bago City', '', '', 0),
(15, 'Ma-ao', 10.49019, 122.99165, 'Bago City', '', '', 0),
(16, 'Mailum', 10.46211, 123.0492, 'Bago City', '', '', 0),
(17, 'Malingin', 10.49395, 122.91783, 'Bago City', 'https://www.facebook.com/BrgyMalingin', 'Brgy Malingin Official', 0),
(18, 'Napoles', 10.51267, 122.89781, 'Bago City', '', '', 0),
(19, 'Pacol', 10.49507, 122.86697, 'Bago City', 'https://www.facebook.com/SKPacol', 'SK Brgy. Pacol', 0),
(20, 'Poblacion', 10.54115, 122.83539, 'Bago City', '', '', 0),
(21, 'Sagasa', 10.46983, 122.89283, 'Bago City', '', '', 0),
(22, 'Tabunan', 10.57625, 122.93727, 'Bago City', '', '', 0),
(23, 'Taloc', 10.5873, 122.90942, 'Bago City', '', '', 0),
(24, 'Sampinit', 10.54426, 122.85341, 'Bago City', '', '', 0),
(25, 'Sum ag', 10.607152, 122.921364, 'Bago City', '', '', 0),
(26, 'Sum ag2', 10.606997, 122.921277, 'Bago City', '', '', 0),
(27, 'cb2/office', 10.530443, 122.842643, 'Bago City', '', '', 0),
(28, 'cb5', 10.530531, 122.842419, 'Bago City', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `client_notifications`
--

CREATE TABLE `client_notifications` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `title` varchar(225) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `request_id` varchar(20) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_notifications`
--

INSERT INTO `client_notifications` (`id`, `client_id`, `title`, `notification_type`, `message`, `request_id`, `is_read`, `created_at`) VALUES
(1, 9, '', 'approved', 'Your request for Grass-Cutting on 2025-08-07 has been approved. Admin notes: done', 'REQ202508056621', 1, '2025-08-06 01:49:38'),
(2, 9, '', 'approved', 'Your request for Street Cleaning on 2025-08-06 has been approved. Admin notes: 12312', 'REQ202508055999', 1, '2025-08-06 02:57:38'),
(3, 9, '', 'approved', 'Your request for Grass-Cutting on 2025-08-06 has been approved. Admin notes: test\r\n', 'REQ202508054123', 0, '2025-08-06 02:58:58');

-- --------------------------------------------------------

--
-- Table structure for table `client_requests`
--

CREATE TABLE `client_requests` (
  `id` int(11) NOT NULL,
  `request_id` varchar(20) NOT NULL,
  `client_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_email` varchar(255) NOT NULL,
  `client_contact` varchar(20) NOT NULL,
  `client_barangay` varchar(255) NOT NULL,
  `request_date` date NOT NULL,
  `request_time` time NOT NULL,
  `request_details` varchar(255) NOT NULL,
  `request_description` text DEFAULT NULL,
  `title` varchar(225) NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `request_type` varchar(100) NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_requests`
--

INSERT INTO `client_requests` (`id`, `request_id`, `client_id`, `client_name`, `client_email`, `client_contact`, `client_barangay`, `request_date`, `request_time`, `request_details`, `request_description`, `title`, `status`, `admin_notes`, `submitted_at`, `updated_at`, `request_type`, `preferred_date`, `created_at`) VALUES
(1, 'REQ202508056621', 9, 'test test', 'test@gmail.com', '09123456784', 'Pacol', '2025-08-07', '08:00:00', 'Grass-Cutting', 'grass cutter', '', 'approved', 'done', '2025-08-05 19:48:53', '2025-08-06 01:49:38', '', NULL, '2025-08-06 05:33:50'),
(2, 'REQ202508057147', 9, 'test test', 'test@gmail.com', '09123456784', 'Pacol', '2025-08-15', '09:00:00', 'Garbage Collection', 'Bday', '', 'rejected', 'asaa', '2025-08-05 20:14:57', '2025-08-06 05:22:14', '', NULL, '2025-08-06 05:33:50'),
(3, 'REQ202508051880', 9, 'test test', 'test@gmail.com', '09123456784', 'Pacol', '2025-08-09', '08:00:00', 'Grass-Cutting', 'grass cutter', '', 'rejected', 'fck that', '2025-08-05 20:32:21', '2025-08-06 05:20:44', '', NULL, '2025-08-06 05:33:50'),
(4, 'REQ202508055999', 9, 'test test', 'test@gmail.com', '09123456784', 'Pacol', '2025-08-06', '08:00:00', 'Street Cleaning', '', '', 'approved', '12312', '2025-08-05 20:46:47', '2025-08-06 02:57:38', '', NULL, '2025-08-06 05:33:50'),
(5, 'REQ202508054123', 9, 'test test', 'test@gmail.com', '09123456784', 'Pacol', '2025-08-06', '09:00:00', 'Grass-Cutting', '', '', 'approved', 'test\r\n', '2025-08-05 20:51:23', '2025-08-06 02:58:58', '', NULL, '2025-08-06 05:33:50'),
(6, 'REQ202508052062', 9, 'test test', 'test@gmail.com', '09123456784', 'Pacol', '2025-08-07', '08:00:00', 'Garbage Collection', '', '', 'approved', 'tanginamo', '2025-08-05 21:26:40', '2025-08-06 05:18:42', '', NULL, '2025-08-06 05:33:50'),
(7, '', 9, 'test test', 'test@gmail.com', '09123456784', 'Pacol', '0000-00-00', '00:00:00', 'Garbage Collection', 'wqwqwq', '', 'pending', NULL, '2025-08-06 05:34:00', '2025-08-06 05:34:00', 'Garbage Collection', '2025-08-13', '2025-08-06 05:34:00');

-- --------------------------------------------------------

--
-- Table structure for table `client_table`
--

CREATE TABLE `client_table` (
  `client_id` int(225) NOT NULL,
  `first_name` varchar(225) NOT NULL,
  `last_name` varchar(225) NOT NULL,
  `contact` varchar(11) NOT NULL,
  `email` varchar(225) NOT NULL,
  `barangay` varchar(225) NOT NULL,
  `password` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_table`
--

INSERT INTO `client_table` (`client_id`, `first_name`, `last_name`, `contact`, `email`, `barangay`, `password`) VALUES
(2, 'Joshua', 'Elgario', '09123123123', 'jack@gmail.com', 'Alianza', '$2y$10$9ytvFSOUOOLQRrrKBcXs3Occ3FWW.0XBy9mxYhHh8jfYakLeldLfS'),
(9, 'test', 'test', '09123456784', 'test@gmail.com', 'Pacol', '$2y$10$.g86SZ1A4pYzSIn2bXFH2e45Q1Lnrsxvv95GeBrr51Xb78jHMSYq6');

-- --------------------------------------------------------

--
-- Table structure for table `driver_table`
--

CREATE TABLE `driver_table` (
  `driver_id` int(6) NOT NULL,
  `first_name` varchar(225) NOT NULL,
  `last_name` varchar(225) NOT NULL,
  `address` varchar(225) NOT NULL,
  `contact` varchar(11) NOT NULL,
  `age` int(225) NOT NULL,
  `gender` varchar(225) NOT NULL,
  `password` varchar(225) NOT NULL,
  `location_id` int(225) NOT NULL,
  `license_no` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_table`
--

INSERT INTO `driver_table` (`driver_id`, `first_name`, `last_name`, `address`, `contact`, `age`, `gender`, `password`, `location_id`, `license_no`) VALUES
(1, 'Angel', 'Adlaon', 'Malingin', '11111111111', 30, 'Male', 'ren123', 1, 'MNL ID 1234');

-- --------------------------------------------------------

--
-- Table structure for table `dumpsite_table`
--

CREATE TABLE `dumpsite_table` (
  `dump_id` int(11) NOT NULL,
  `dumpsite_capacity` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gps_location`
--

CREATE TABLE `gps_location` (
  `location_id` int(11) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gps_location`
--

INSERT INTO `gps_location` (`location_id`, `latitude`, `longitude`, `timestamp`) VALUES
(1, 10.530723, 122.84263, '0000-00-00 00:00:00'),
(2, 10.530723, 122.84263, '0000-00-00 00:00:00'),
(3, 10.530713, 122.84261, '0000-00-00 00:00:00'),
(4, 10.530713, 122.84261, '0000-00-00 00:00:00'),
(5, 10.530709, 122.84263, '0000-00-00 00:00:00'),
(6, 10.530709, 122.84263, '0000-00-00 00:00:00'),
(7, 10.530654, 122.84266, '0000-00-00 00:00:00'),
(8, 10.530654, 122.84266, '0000-00-00 00:00:00'),
(9, 10.530627, 122.84268, '0000-00-00 00:00:00'),
(10, 10.530627, 122.84268, '0000-00-00 00:00:00'),
(11, 10.530626, 122.8427, '0000-00-00 00:00:00'),
(12, 10.530626, 122.8427, '0000-00-00 00:00:00'),
(13, 10.530677, 122.84276, '0000-00-00 00:00:00'),
(14, 10.530677, 122.84276, '0000-00-00 00:00:00'),
(15, 10.530662, 122.84277, '0000-00-00 00:00:00'),
(16, 10.530662, 122.84277, '0000-00-00 00:00:00'),
(17, 10.530665, 122.8428, '0000-00-00 00:00:00'),
(18, 10.530665, 122.8428, '0000-00-00 00:00:00'),
(19, 10.530683, 122.8428, '0000-00-00 00:00:00'),
(20, 10.530683, 122.8428, '0000-00-00 00:00:00'),
(21, 10.530688, 122.84281, '0000-00-00 00:00:00'),
(22, 10.530688, 122.84281, '0000-00-00 00:00:00'),
(23, 10.530696, 122.84283, '0000-00-00 00:00:00'),
(24, 10.530696, 122.84283, '0000-00-00 00:00:00'),
(25, 10.530698, 122.84284, '0000-00-00 00:00:00'),
(26, 10.530698, 122.84284, '0000-00-00 00:00:00'),
(27, 10.530657, 122.8428, '0000-00-00 00:00:00'),
(28, 10.530657, 122.8428, '0000-00-00 00:00:00'),
(29, 10.530653, 122.84276, '0000-00-00 00:00:00'),
(30, 10.530653, 122.84276, '0000-00-00 00:00:00'),
(31, 10.530636, 122.84276, '0000-00-00 00:00:00'),
(32, 10.530636, 122.84276, '0000-00-00 00:00:00'),
(33, 10.530622, 122.84274, '0000-00-00 00:00:00'),
(34, 10.530622, 122.84274, '0000-00-00 00:00:00'),
(35, 10.530626, 122.84273, '0000-00-00 00:00:00'),
(36, 10.530626, 122.84273, '0000-00-00 00:00:00'),
(37, 10.530649, 122.84274, '0000-00-00 00:00:00'),
(38, 10.530649, 122.84274, '0000-00-00 00:00:00'),
(39, 10.530629, 122.8427, '0000-00-00 00:00:00'),
(40, 10.530629, 122.8427, '0000-00-00 00:00:00'),
(41, 10.530635, 122.84272, '0000-00-00 00:00:00'),
(42, 10.530635, 122.84272, '0000-00-00 00:00:00'),
(43, 10.530679, 122.8428, '0000-00-00 00:00:00'),
(44, 10.530679, 122.8428, '0000-00-00 00:00:00'),
(45, 10.530664, 122.8428, '0000-00-00 00:00:00'),
(46, 10.530664, 122.8428, '0000-00-00 00:00:00'),
(47, 10.530672, 122.84274, '0000-00-00 00:00:00'),
(48, 10.530672, 122.84274, '0000-00-00 00:00:00'),
(49, 10.530716, 122.84277, '0000-00-00 00:00:00'),
(50, 10.530716, 122.84277, '0000-00-00 00:00:00'),
(51, 10.530764, 122.84278, '0000-00-00 00:00:00'),
(52, 10.530764, 122.84278, '0000-00-00 00:00:00'),
(53, 10.530775, 122.84277, '0000-00-00 00:00:00'),
(54, 10.530775, 122.84277, '0000-00-00 00:00:00'),
(55, 10.530734, 122.84272, '0000-00-00 00:00:00'),
(56, 10.530734, 122.84272, '0000-00-00 00:00:00'),
(57, 10.530702, 122.8427, '0000-00-00 00:00:00'),
(58, 10.530702, 122.8427, '0000-00-00 00:00:00'),
(59, 10.530671, 122.84269, '0000-00-00 00:00:00'),
(60, 10.530671, 122.84269, '0000-00-00 00:00:00'),
(61, 10.530627, 122.84268, '0000-00-00 00:00:00'),
(62, 10.530627, 122.84268, '0000-00-00 00:00:00'),
(63, 10.53062, 122.84267, '0000-00-00 00:00:00'),
(64, 10.53062, 122.84267, '0000-00-00 00:00:00'),
(65, 10.530563, 122.84259, '0000-00-00 00:00:00'),
(66, 10.530563, 122.84259, '0000-00-00 00:00:00'),
(67, 10.530518, 122.84255, '0000-00-00 00:00:00'),
(68, 10.530518, 122.84255, '0000-00-00 00:00:00'),
(69, 10.530563, 122.84261, '0000-00-00 00:00:00'),
(70, 10.530563, 122.84261, '0000-00-00 00:00:00'),
(71, 10.53058, 122.84265, '0000-00-00 00:00:00'),
(72, 10.53058, 122.84265, '0000-00-00 00:00:00'),
(73, 10.530577, 122.84288, '0000-00-00 00:00:00'),
(74, 10.530577, 122.84288, '0000-00-00 00:00:00'),
(75, 10.530595, 122.84299, '0000-00-00 00:00:00'),
(76, 10.530595, 122.84299, '0000-00-00 00:00:00'),
(77, 10.530566, 122.8432, '0000-00-00 00:00:00'),
(78, 10.530566, 122.8432, '0000-00-00 00:00:00'),
(79, 10.530548, 122.84337, '0000-00-00 00:00:00'),
(80, 10.530548, 122.84337, '0000-00-00 00:00:00'),
(81, 10.53054, 122.84341, '0000-00-00 00:00:00'),
(82, 10.53054, 122.84341, '0000-00-00 00:00:00'),
(83, 10.530472, 122.84335, '0000-00-00 00:00:00'),
(84, 10.530472, 122.84335, '0000-00-00 00:00:00'),
(85, 10.530509, 122.84331, '0000-00-00 00:00:00'),
(86, 10.530509, 122.84331, '0000-00-00 00:00:00'),
(87, 10.530584, 122.84325, '0000-00-00 00:00:00'),
(88, 10.530584, 122.84325, '0000-00-00 00:00:00'),
(89, 10.530634, 122.84315, '0000-00-00 00:00:00'),
(90, 10.530634, 122.84315, '0000-00-00 00:00:00'),
(91, 10.530614, 122.84302, '0000-00-00 00:00:00'),
(92, 10.530614, 122.84302, '0000-00-00 00:00:00'),
(93, 10.530636, 122.8429, '0000-00-00 00:00:00'),
(94, 10.530636, 122.8429, '0000-00-00 00:00:00'),
(95, 10.530663, 122.84292, '0000-00-00 00:00:00'),
(96, 10.530663, 122.84292, '0000-00-00 00:00:00'),
(97, 10.530719, 122.84286, '0000-00-00 00:00:00'),
(98, 10.530719, 122.84286, '0000-00-00 00:00:00'),
(99, 10.530739, 122.84279, '0000-00-00 00:00:00'),
(100, 10.530739, 122.84279, '0000-00-00 00:00:00'),
(101, 10.53075, 122.84277, '0000-00-00 00:00:00'),
(102, 10.53075, 122.84277, '0000-00-00 00:00:00'),
(103, 10.530749, 122.84276, '0000-00-00 00:00:00'),
(104, 10.530749, 122.84276, '0000-00-00 00:00:00'),
(105, 10.530751, 122.84277, '0000-00-00 00:00:00'),
(106, 10.530751, 122.84277, '0000-00-00 00:00:00'),
(107, 10.530743, 122.8428, '0000-00-00 00:00:00'),
(108, 10.530743, 122.8428, '0000-00-00 00:00:00'),
(109, 10.530742, 122.84282, '0000-00-00 00:00:00'),
(110, 10.530742, 122.84282, '0000-00-00 00:00:00'),
(111, 10.530735, 122.84283, '0000-00-00 00:00:00'),
(112, 10.530735, 122.84283, '0000-00-00 00:00:00'),
(113, 10.530734, 122.84281, '0000-00-00 00:00:00'),
(114, 10.530734, 122.84281, '0000-00-00 00:00:00'),
(115, 10.530734, 122.84276, '0000-00-00 00:00:00'),
(116, 10.530734, 122.84276, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_table`
--

CREATE TABLE `maintenance_table` (
  `maintenance_id` int(11) NOT NULL,
  `m_name` varchar(225) NOT NULL,
  `m_date` date NOT NULL,
  `m_time` time NOT NULL,
  `status` varchar(11) NOT NULL,
  `waste_service_id` int(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `route_table`
--

CREATE TABLE `route_table` (
  `route_id` int(225) NOT NULL,
  `brgy_id` int(225) NOT NULL,
  `waste_service_id` int(225) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `start_point` varchar(225) NOT NULL,
  `end_point` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_table`
--

INSERT INTO `route_table` (`route_id`, `brgy_id`, `waste_service_id`, `driver_id`, `start_point`, `end_point`) VALUES
(1, 14, 1, 1, 'Bago City Hall', 'cb2/office');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_table`
--

CREATE TABLE `schedule_table` (
  `schedule_id` int(11) NOT NULL,
  `event_name` varchar(11) NOT NULL,
  `day` varchar(225) NOT NULL,
  `time` time NOT NULL,
  `status` varchar(225) NOT NULL,
  `waste_service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_table`
--

INSERT INTO `schedule_table` (`schedule_id`, `event_name`, `day`, `time`, `status`, `waste_service_id`) VALUES
(1, '', 'Monday', '08:00:00', 'Assigned', 1),
(2, '', 'Tuesday ', '00:00:00', 'Vacant', 1),
(3, '', 'Wednesday', '00:00:00', 'Pending', 1),
(4, '', 'Thursday', '00:00:00', 'Waste Collected	', 1),
(5, '', 'Friday', '00:00:00', 'Waste Collected	', 1),
(6, '', 'Saturday', '00:00:00', 'Pending', 1),
(7, '', 'Sunday', '00:00:00', 'Vacant', 1),
(43, 'Cutting', '2025-07-29', '00:00:00', 'Scheduled', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sensor`
--

CREATE TABLE `sensor` (
  `sensor_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_assignment_table`
--

CREATE TABLE `service_assignment_table` (
  `service_ass_id` int(11) NOT NULL,
  `waste_service_id` int(11) NOT NULL,
  `vehicle_type` varchar(225) NOT NULL,
  `schedule_id` int(225) NOT NULL,
  `brgy_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_assignment_table`
--

INSERT INTO `service_assignment_table` (`service_ass_id`, `waste_service_id`, `vehicle_type`, `schedule_id`, `brgy_id`) VALUES
(1, 1, 'Dump Truck', 1, 12);

-- --------------------------------------------------------

--
-- Table structure for table `service_schedules`
--

CREATE TABLE `service_schedules` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('available','unavailable','maintenance') DEFAULT 'available',
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_schedules`
--

INSERT INTO `service_schedules` (`id`, `date`, `status`, `reason`, `created_by`, `created_at`) VALUES
(1, '2024-01-06', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(2, '2024-01-07', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(3, '2024-01-13', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(4, '2024-01-14', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(5, '2024-01-20', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(6, '2024-01-21', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(7, '2024-01-27', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(8, '2024-01-28', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(9, '2024-02-03', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(10, '2024-02-04', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(11, '2024-02-10', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(12, '2024-02-11', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(13, '2024-02-17', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(14, '2024-02-18', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(15, '2024-02-24', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52'),
(16, '2024-02-25', 'unavailable', 'Weekend', NULL, '2025-08-06 01:46:52');

-- --------------------------------------------------------

--
-- Table structure for table `tracking_table`
--

CREATE TABLE `tracking_table` (
  `tracking_id` int(225) NOT NULL,
  `route_id` int(225) NOT NULL,
  `status` varchar(225) NOT NULL,
  `date_time` datetime NOT NULL,
  `location_id` int(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waste_collection_table`
--

CREATE TABLE `waste_collection_table` (
  `waste_collection_id` int(11) NOT NULL,
  `day` date NOT NULL,
  `time` time NOT NULL,
  `waste_service_id` int(11) NOT NULL,
  `status` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waste_management`
--

CREATE TABLE `waste_management` (
  `waste_management_id` int(11) NOT NULL,
  `waste_collection_id` int(11) NOT NULL,
  `dump_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waste_service_table`
--

CREATE TABLE `waste_service_table` (
  `waste_service_id` int(6) NOT NULL,
  `vehicle_name` varchar(225) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `vehicle_capacity` varchar(225) NOT NULL,
  `schedule_id` int(225) NOT NULL,
  `maintenance_id` int(225) NOT NULL,
  `route_id` int(11) NOT NULL,
  `plate_no` int(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waste_service_table`
--

INSERT INTO `waste_service_table` (`waste_service_id`, `vehicle_name`, `driver_id`, `vehicle_capacity`, `schedule_id`, `maintenance_id`, `route_id`, `plate_no`) VALUES
(1, 'Vehicle 1', 1, '3 - 5 tons', 1, 2, 1, 3210);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_type` (`notification_type`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `admin_table`
--
ALTER TABLE `admin_table`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `barangays_table`
--
ALTER TABLE `barangays_table`
  ADD PRIMARY KEY (`brgy_id`);

--
-- Indexes for table `client_notifications`
--
ALTER TABLE `client_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `notification_type` (`notification_type`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `client_requests`
--
ALTER TABLE `client_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `status` (`status`),
  ADD KEY `request_date` (`request_date`);

--
-- Indexes for table `client_table`
--
ALTER TABLE `client_table`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `driver_table`
--
ALTER TABLE `driver_table`
  ADD PRIMARY KEY (`driver_id`);

--
-- Indexes for table `dumpsite_table`
--
ALTER TABLE `dumpsite_table`
  ADD PRIMARY KEY (`dump_id`);

--
-- Indexes for table `gps_location`
--
ALTER TABLE `gps_location`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `maintenance_table`
--
ALTER TABLE `maintenance_table`
  ADD PRIMARY KEY (`maintenance_id`);

--
-- Indexes for table `route_table`
--
ALTER TABLE `route_table`
  ADD PRIMARY KEY (`route_id`);

--
-- Indexes for table `schedule_table`
--
ALTER TABLE `schedule_table`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `sensor`
--
ALTER TABLE `sensor`
  ADD PRIMARY KEY (`sensor_id`);

--
-- Indexes for table `service_assignment_table`
--
ALTER TABLE `service_assignment_table`
  ADD PRIMARY KEY (`service_ass_id`);

--
-- Indexes for table `service_schedules`
--
ALTER TABLE `service_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `tracking_table`
--
ALTER TABLE `tracking_table`
  ADD PRIMARY KEY (`tracking_id`);

--
-- Indexes for table `waste_collection_table`
--
ALTER TABLE `waste_collection_table`
  ADD PRIMARY KEY (`waste_collection_id`);

--
-- Indexes for table `waste_management`
--
ALTER TABLE `waste_management`
  ADD PRIMARY KEY (`waste_management_id`);

--
-- Indexes for table `waste_service_table`
--
ALTER TABLE `waste_service_table`
  ADD PRIMARY KEY (`waste_service_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admin_table`
--
ALTER TABLE `admin_table`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `barangays_table`
--
ALTER TABLE `barangays_table`
  MODIFY `brgy_id` int(24) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `client_notifications`
--
ALTER TABLE `client_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `client_requests`
--
ALTER TABLE `client_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `client_table`
--
ALTER TABLE `client_table`
  MODIFY `client_id` int(225) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `driver_table`
--
ALTER TABLE `driver_table`
  MODIFY `driver_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dumpsite_table`
--
ALTER TABLE `dumpsite_table`
  MODIFY `dump_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gps_location`
--
ALTER TABLE `gps_location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `maintenance_table`
--
ALTER TABLE `maintenance_table`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `route_table`
--
ALTER TABLE `route_table`
  MODIFY `route_id` int(225) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schedule_table`
--
ALTER TABLE `schedule_table`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `sensor`
--
ALTER TABLE `sensor`
  MODIFY `sensor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_assignment_table`
--
ALTER TABLE `service_assignment_table`
  MODIFY `service_ass_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_schedules`
--
ALTER TABLE `service_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tracking_table`
--
ALTER TABLE `tracking_table`
  MODIFY `tracking_id` int(225) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `waste_collection_table`
--
ALTER TABLE `waste_collection_table`
  MODIFY `waste_collection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `waste_management`
--
ALTER TABLE `waste_management`
  MODIFY `waste_management_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `waste_service_table`
--
ALTER TABLE `waste_service_table`
  MODIFY `waste_service_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
