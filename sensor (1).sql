-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2025 at 09:45 PM
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
-- Table structure for table `sensor`
--

CREATE TABLE `sensor` (
  `id` int(11) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `count` int(225) NOT NULL,
  `location_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `distance` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensor`
--

INSERT INTO `sensor` (`id`, `sensor_id`, `count`, `location_id`, `timestamp`, `distance`) VALUES
(1, 1, 250, 1754767868, '2025-08-16 21:38:21', NULL),
(2, 1, 300, 1754767868, '2025-08-16 21:38:21', NULL),
(3, 1, 320, 1754767868, '2025-08-04 05:00:00', NULL),
(4, 1, 410, 1754767868, '2025-08-05 05:00:00', NULL),
(5, 1, 250, 1754767868, '2025-08-12 05:00:00', NULL),
(6, 1, 350, 1754767868, '2025-08-13 05:00:00', NULL),
(7, 1, 500, 1754767868, '2025-08-14 05:00:00', NULL),
(8, 1, 460, 1754767868, '2025-08-15 05:00:00', NULL),
(11, 1, 320, 1754767868, '2025-08-11 05:00:00', NULL),
(12, 1, 250, 1754767868, '2025-08-12 05:00:00', NULL),
(13, 1, 350, 1754767868, '2025-08-13 05:00:00', NULL),
(14, 1, 500, 1754767868, '2025-08-14 05:00:00', NULL),
(15, 1, 460, 1754767868, '2025-08-15 05:00:00', NULL),
(16, 1, 0, 1754767868, '2025-08-16 05:00:00', NULL),
(18, 1, 500, 0, '2025-08-20 03:24:02', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sensor`
--
ALTER TABLE `sensor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sensor_id` (`sensor_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sensor`
--
ALTER TABLE `sensor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
