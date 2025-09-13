-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2025 at 07:53 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `disaster_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `disasters`
--

CREATE TABLE `disasters` (
  `id` int(11) NOT NULL,
  `disaster_type` varchar(100) NOT NULL,
  `location` varchar(150) NOT NULL,
  `emergency_contact` varchar(100) NOT NULL,
  `date_reported` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disasters`
--

INSERT INTO `disasters` (`id`, `disaster_type`, `location`, `emergency_contact`, `date_reported`) VALUES
(1, 'TYPHOON \" REMING\"', 'ALBAY', '911', '2025-08-16 15:50:46'),
(2, 'Flash Flood', 'LEGAZPI CITY', '911', '2025-08-17 06:03:50'),
(3, 'FLASH FLOOD', 'SORSOGON CITY', '911 SORSOGON', '2025-08-18 10:51:32');

-- --------------------------------------------------------

--
-- Table structure for table `drills`
--

CREATE TABLE `drills` (
  `id` int(11) NOT NULL,
  `drill_name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `participants` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drills`
--

INSERT INTO `drills` (`id`, `drill_name`, `location`, `date`, `participants`, `remarks`, `created_at`) VALUES
(1, 'Earthquake Drill', 'DSWD REGION V BURAGUIS LEGAZPI CTIY', '2025-08-17', 1000, 'SUCCESFULLY ACCOMPLISHED', '2025-08-17 14:03:44'),
(6, 'Earthquake Drill', 'BAGUMBAYAN CENTRAL SCHOOLS', '2025-08-18', 400, 'SUCCESSFULY ACCOMPLISHED', '2025-08-17 14:43:15');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `quantity`, `location`, `date_added`) VALUES
(1, 'NFA SACKS', 1000, 'LEGAZPI CITY', '2025-08-17 08:06:50'),
(2, 'CORNED BEEF  250 GRAM', 1000, 'LEGAZPI CITY', '2025-08-17 08:36:41'),
(3, 'SARDINES ', 1000, 'LEGAZPI CITY', '2025-08-17 14:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`) VALUES
(2, 'lito900', '$2y$10$/YuHEAbShClBjyP/O1WBSeAiZXCjuakEpEYp4WMzGTabs7p/EgG7K', 'lito Selencio', 'admin', '2025-08-16 14:56:03'),
(4, 'user', '$2y$10$vnJfICd/UmZZEf98Q7ylluBDgH1MMtTQqswGgs8Ro99nzmTLHDiem', 'boy pula', 'viewer', '2025-08-16 16:40:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `disasters`
--
ALTER TABLE `disasters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drills`
--
ALTER TABLE `drills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `disasters`
--
ALTER TABLE `disasters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `drills`
--
ALTER TABLE `drills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
