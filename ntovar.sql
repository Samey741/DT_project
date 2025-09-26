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
CREATE TABLE IF NOT EXISTS `ntovar` (
  `id` varchar(30) COLLATE utf8mb3_slovak_ci NOT NULL,
  `pc` int DEFAULT NULL,
  `nazov` varchar(20) COLLATE utf8mb3_slovak_ci DEFAULT NULL,
  `vyrobca` varchar(20) COLLATE utf8mb3_slovak_ci DEFAULT NULL,
  `popis` varchar(100) COLLATE utf8mb3_slovak_ci DEFAULT NULL,
  `kusov` int DEFAULT NULL,
  `cena` int DEFAULT NULL,
  `kod` varchar(20) COLLATE utf8mb3_slovak_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_slovak_ci;

--
-- Sťahujem dáta pre tabuľku `ntovar`
--

INSERT INTO `ntovar` (`id`, `pc`, `nazov`, `vyrobca`, `popis`, `kusov`, `cena`, `kod`) VALUES
('20231105160340', 1, 'Názov produktu', 'Výrobca', '0', 10, 100, '0'),
('20231105161104', 2, 'Fabia', 'Skoda', '1', 2, 1500, '0');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
