-------------------------------------------
----------- ENTIRE DB STRUCTURE -----------
-------------------------------------------

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2025 at 10:39 AM
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
-- Database: `dt_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `ntovar`
--

CREATE TABLE `ntovar` (
  `id` varchar(30) NOT NULL,
  `pc` varchar(30) DEFAULT NULL,
  `nazov` varchar(20) DEFAULT NULL,
  `vyrobca` varchar(20) DEFAULT NULL,
  `popis` varchar(100) DEFAULT NULL,
  `kusov` int(11) DEFAULT NULL,
  `cena` int(11) DEFAULT NULL,
  `kod` varchar(20) DEFAULT NULL,
  `node_origin` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;

-- --------------------------------------------------------

--
-- Table structure for table `replication_queue`
--

CREATE TABLE `replication_queue` (
  `id` varchar(120) NOT NULL,
  `repl_id` varchar(50) NOT NULL,
  `node_id` varchar(50) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `status` enum('pending','done','failed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ntovar`
--
ALTER TABLE `ntovar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `replication_queue`
--
ALTER TABLE `replication_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_repl_node` (`repl_id`,`node_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
