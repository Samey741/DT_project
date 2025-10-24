-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hostiteľ: 127.0.0.1:3306
-- Čas generovania: Sun 05.Nov 2023, 16:32
-- Verzia serveru: 8.0.31
-- Verzia PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáza: `dsd`
--

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `ntovar`
--

DROP TABLE IF EXISTS `ntovar`;

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
