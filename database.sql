-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db.dw041.nameserver.sk
-- Generation Time: Mar 31, 2025 at 06:22 AM
-- Server version: 5.5.62-log
-- PHP Version: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `baptistsk4`
--

-- --------------------------------------------------------

--
-- Table structure for table `aktivity`
--

CREATE TABLE `aktivity` (
  `id` int(11) NOT NULL,
  `nazov` varchar(100) NOT NULL,
  `den` enum('streda','stvrtok','piatok') NOT NULL,
  `kapacita` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `aktivity`
--

INSERT INTO `aktivity` (`id`, `nazov`, `den`, `kapacita`) VALUES
(1, 'Chvalospevka', 'streda', 10),
(2, 'Chvalospevka', 'stvrtok', 10),
(3, 'Chvalospevka', 'piatok', 10),
(4, 'Biblický seminár', 'streda', 10),
(5, 'Biblický seminár', 'stvrtok', 10),
(6, 'Biblický seminár', 'piatok', 10),
(7, 'Umelecký ateliér', 'streda', 10),
(8, 'Umelecký ateliér', 'stvrtok', 10),
(9, 'Športy', 'streda', 10),
(10, 'Športy', 'stvrtok', 10),
(11, 'Športy', 'piatok', 10),
(12, 'Dráma', 'piatok', 10),
(13, 'Choreografia', 'stvrtok', 10);

-- --------------------------------------------------------

--
-- Table structure for table `alergie`
--

CREATE TABLE `alergie` (
  `id` int(11) NOT NULL,
  `nazov` varchar(255) NOT NULL,
  `popis` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `alergie`
--

INSERT INTO `alergie` (`id`, `nazov`, `popis`) VALUES
(1, 'Lepok', 'Pšenica, raž, jačmeň, ovos, špalda, kamut alebo ich hybridné odrody.'),
(2, 'Vajcia', 'Alergia na vajcia a výrobky z nich.'),
(3, 'Ryby', 'Ryby a výrobky z nich.'),
(4, 'Arašidy', 'Alergia na arašidy a výrobky z nich.'),
(5, 'Sójové zrná', 'Sójové produkty vrátane sójového mlieka a tofu.'),
(6, 'Mlieko', 'Alergia na mlieko, mliečne bielkoviny, mliečne výrobky.'),
(7, 'Orechy', 'Mandle, lieskové orechy, vlašské orechy, kešu, pekanové orechy, para orechy, pistácie, makadamové orechy.'),
(8, 'Horčica', 'Horčica a výrobky z nej.'),
(9, 'Sezamové semienka', 'Alergia na sezam a výrobky z neho.'),
(10, 'Histamín', 'Obmedzenie potravín s vysokým obsahom histamínu.'),
(11, 'Citrusy', 'Alergia na citrusové ovocie, ako sú pomaranče, citróny, limetky.'),
(12, 'Penicilín', 'Alergia na antibiotikum penicilín.');

-- --------------------------------------------------------

--
-- Stand-in structure for view `dostupne_ubytovanie`
-- (See below for the actual view)
--
CREATE TABLE `dostupne_ubytovanie` (
`id` int(11)
,`izba` varchar(50)
,`kapacita` int(11)
,`typ` enum('muz','zena','veduci','spolocne')
,`obsadene` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `mladez`
--

CREATE TABLE `mladez` (
  `id` int(11) NOT NULL,
  `nazov` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mladez`
--

INSERT INTO `mladez` (`id`, `nazov`) VALUES
(3, 'Bratislava Palisády'),
(5, 'Košice'),
(4, 'Lučenec'),
(1, 'SHIFT Komárno'),
(2, 'Teen-Z-One Viera');

-- --------------------------------------------------------

--
-- Table structure for table `os_udaje`
--

CREATE TABLE `os_udaje` (
  `id` int(11) NOT NULL,
  `meno` varchar(50) NOT NULL,
  `priezvisko` varchar(50) NOT NULL,
  `datum_narodenia` date NOT NULL,
  `pohlavie` enum('M','F') NOT NULL,
  `mladez` varchar(100) DEFAULT NULL,
  `poznamka` text,
  `mail` varchar(100) NOT NULL,
  `novy` tinyint(1) DEFAULT '1',
  `ucastnik` enum('taborujuci','veduci','host') DEFAULT 'taborujuci',
  `GDPR` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `os_udaje`
--

INSERT INTO `os_udaje` (`id`, `meno`, `priezvisko`, `datum_narodenia`, `pohlavie`, `mladez`, `poznamka`, `mail`, `novy`, `ucastnik`, `GDPR`) VALUES
(1, 'martin', 'micuch', '2004-06-07', 'M', 'SHIFT Komï¿½rno', '', 'micuchmartin19@gmail.com', 0, 'taborujuci', 1),
(3, 'bibi', 'bonova', '2006-01-21', 'F', 'SHIFT Komï¿½rno', '', 'bibibonova@gmail.com', 0, 'taborujuci', 1),
(4, 'bibi', 'bonova', '2006-01-21', 'F', 'SHIFT Komï¿½rno', '', 'bibibonova3@gmail.com', 0, NULL, 1),
(5, 'timo', 'bodzas', '1994-08-06', 'M', 'SHIFT Komï¿½rno', '', 'timobodzas@gmail.com', 0, NULL, 1),
(6, 'test', 'test', '2006-07-06', 'F', 'Lu?enec', '', 'idk@gmail.com', 1, 'taborujuci', 1);

-- --------------------------------------------------------

--
-- Table structure for table `os_udaje_aktivity`
--

CREATE TABLE `os_udaje_aktivity` (
  `os_udaje_id` int(11) NOT NULL DEFAULT '0',
  `aktivita_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `os_udaje_aktivity`
--

INSERT INTO `os_udaje_aktivity` (`os_udaje_id`, `aktivita_id`) VALUES
(6, 1),
(1, 2),
(6, 3),
(5, 4),
(5, 5),
(1, 7),
(3, 8),
(4, 8),
(3, 9),
(4, 9),
(1, 11),
(5, 11),
(3, 12),
(4, 12),
(6, 13);

-- --------------------------------------------------------

--
-- Table structure for table `os_udaje_alergie`
--

CREATE TABLE `os_udaje_alergie` (
  `os_udaje_id` int(11) NOT NULL DEFAULT '0',
  `alergie_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `os_udaje_alergie`
--

INSERT INTO `os_udaje_alergie` (`os_udaje_id`, `alergie_id`) VALUES
(5, 2),
(3, 6),
(4, 6),
(6, 8),
(1, 10),
(6, 11);

-- --------------------------------------------------------

--
-- Table structure for table `os_udaje_ubytovanie`
--

CREATE TABLE `os_udaje_ubytovanie` (
  `os_udaje_id` int(11) NOT NULL DEFAULT '0',
  `ubytovanie_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `os_udaje_ubytovanie`
--

INSERT INTO `os_udaje_ubytovanie` (`os_udaje_id`, `ubytovanie_id`) VALUES
(4, 2),
(5, 5),
(6, 5);

-- --------------------------------------------------------

--
-- Table structure for table `ubytovanie`
--

CREATE TABLE `ubytovanie` (
  `id` int(11) NOT NULL,
  `izba` varchar(50) NOT NULL,
  `kapacita` int(11) NOT NULL,
  `typ` enum('muz','zena','veduci','spolocne') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ubytovanie`
--

INSERT INTO `ubytovanie` (`id`, `izba`, `kapacita`, `typ`) VALUES
(1, 'Chata', 15, 'muz'),
(2, 'Chata', 15, 'zena'),
(3, 'Špeciálna izba', 6, 'zena'),
(4, 'Kancelária', 2, 'veduci'),
(5, 'Stan/Hamak', 35, 'spolocne');

-- --------------------------------------------------------

--
-- Structure for view `dostupne_ubytovanie`
--
DROP TABLE IF EXISTS `dostupne_ubytovanie`;

CREATE ALGORITHM=UNDEFINED DEFINER=`db1846`@`%` SQL SECURITY DEFINER VIEW `dostupne_ubytovanie`  AS SELECT `u`.`id` AS `id`, `u`.`izba` AS `izba`, `u`.`kapacita` AS `kapacita`, `u`.`typ` AS `typ`, (select count(0) from `os_udaje_ubytovanie` where (`os_udaje_ubytovanie`.`ubytovanie_id` = `u`.`id`)) AS `obsadene` FROM `ubytovanie` AS `u` HAVING (`obsadene` < `kapacita`) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aktivity`
--
ALTER TABLE `aktivity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alergie`
--
ALTER TABLE `alergie`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mladez`
--
ALTER TABLE `mladez`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nazov` (`nazov`);

--
-- Indexes for table `os_udaje`
--
ALTER TABLE `os_udaje`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail` (`mail`);

--
-- Indexes for table `os_udaje_aktivity`
--
ALTER TABLE `os_udaje_aktivity`
  ADD PRIMARY KEY (`os_udaje_id`,`aktivita_id`),
  ADD KEY `aktivita_id` (`aktivita_id`);

--
-- Indexes for table `os_udaje_alergie`
--
ALTER TABLE `os_udaje_alergie`
  ADD PRIMARY KEY (`os_udaje_id`,`alergie_id`),
  ADD KEY `alergie_id` (`alergie_id`);

--
-- Indexes for table `os_udaje_ubytovanie`
--
ALTER TABLE `os_udaje_ubytovanie`
  ADD PRIMARY KEY (`os_udaje_id`,`ubytovanie_id`),
  ADD KEY `ubytovanie_id` (`ubytovanie_id`);

--
-- Indexes for table `ubytovanie`
--
ALTER TABLE `ubytovanie`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aktivity`
--
ALTER TABLE `aktivity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `alergie`
--
ALTER TABLE `alergie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mladez`
--
ALTER TABLE `mladez`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `os_udaje`
--
ALTER TABLE `os_udaje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ubytovanie`
--
ALTER TABLE `ubytovanie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `os_udaje_aktivity`
--
ALTER TABLE `os_udaje_aktivity`
  ADD CONSTRAINT `os_udaje_aktivity_ibfk_1` FOREIGN KEY (`os_udaje_id`) REFERENCES `os_udaje` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `os_udaje_aktivity_ibfk_2` FOREIGN KEY (`aktivita_id`) REFERENCES `aktivity` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `os_udaje_alergie`
--
ALTER TABLE `os_udaje_alergie`
  ADD CONSTRAINT `os_udaje_alergie_ibfk_1` FOREIGN KEY (`os_udaje_id`) REFERENCES `os_udaje` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `os_udaje_alergie_ibfk_2` FOREIGN KEY (`alergie_id`) REFERENCES `alergie` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `os_udaje_ubytovanie`
--
ALTER TABLE `os_udaje_ubytovanie`
  ADD CONSTRAINT `os_udaje_ubytovanie_ibfk_1` FOREIGN KEY (`os_udaje_id`) REFERENCES `os_udaje` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `os_udaje_ubytovanie_ibfk_2` FOREIGN KEY (`ubytovanie_id`) REFERENCES `ubytovanie` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
