-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql:3306
-- Generation Time: Nov 04, 2026 at 10:22 PM
-- Server version: 8.4.13
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gyvunu_paieska`
--

-- --------------------------------------------------------

--
-- Table structure for table `nuotrauka`
--

CREATE TABLE `nuotrauka` (
  `nuotrauka_id` int UNSIGNED NOT NULL,
  `skelbimas_id` int UNSIGNED NOT NULL,
  `failo_kelias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `eiles_nr` smallint UNSIGNED NOT NULL DEFAULT '1',
  `ikelta_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nuotrauka`
--

INSERT INTO `nuotrauka` (`nuotrauka_id`, `skelbimas_id`, `failo_kelias`, `eiles_nr`, `ikelta_at`) VALUES
(1, 3, 'uploads/7d4200d17a6aeceefbb66a4847139140.webp', 1, '2026-11-04 21:53:07');

-- --------------------------------------------------------

--
-- Table structure for table `perziura`
--

CREATE TABLE `perziura` (
  `perziura_id` int UNSIGNED NOT NULL,
  `skelbimas_id` int UNSIGNED NOT NULL,
  `naudotojas_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perziureta_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `perziura`
--

INSERT INTO `perziura` (`perziura_id`, `skelbimas_id`, `naudotojas_id`, `perziureta_at`) VALUES
(1, 3, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:16:06'),
(2, 5, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:17:07'),
(3, 5, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:17:12'),
(4, 3, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:17:14'),
(5, 5, 'a2fe399900de341c39c632244eaf8483', '2026-11-04 22:17:21'),
(6, 3, 'de2cde3560412df8be3d2773e9f1a8b3', '2026-11-04 22:18:46');

-- --------------------------------------------------------

--
-- Table structure for table `skelbimas`
--

CREATE TABLE `skelbimas` (
  `skelbimas_id` int UNSIGNED NOT NULL,
  `autorius_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipas` enum('dinges','rastas') COLLATE utf8mb4_unicode_ci NOT NULL,
  `pavadinimas` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rusis` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lytis` enum('patinas','patele','nezinoma') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amzius_men` smallint UNSIGNED DEFAULT NULL,
  `vieta` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aprasymas` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `galioja_iki` date DEFAULT NULL,
  `busena` enum('aktyvus','pasibaiges','pasalintas','issprestas') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktyvus',
  `sukurtas_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skelbimas`
--

INSERT INTO `skelbimas` (`skelbimas_id`, `autorius_id`, `tipas`, `pavadinimas`, `rusis`, `lytis`, `amzius_men`, `vieta`, `aprasymas`, `galioja_iki`, `busena`, `sukurtas_at`) VALUES
(1, 'de2cde3560412df8be3d2773e9f1a8b3', 'dinges', 'Dingo reksas', 'suo', 'patinas', 16, 'Kaunas, Dainavos rajonas', 'Dingo reksiukas, vokieciu aviganis', '2026-12-04', 'aktyvus', '2026-11-04 21:19:10'),
(3, 'de2cde3560412df8be3d2773e9f1a8b3', 'rastas', 'Radau vokieciu avigani', 'suo', 'patinas', NULL, 'Kaunas centras', 'priglaudziau 060394250', NULL, 'aktyvus', '2026-11-04 21:53:07'),
(5, 'a2fe399900de341c39c632244eaf8483', 'dinges', 'asda', 'asd', 'patinas', 1, 'asd', 'asd', NULL, 'pasalintas', '2026-11-04 22:17:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(30) NOT NULL,
  `password` varchar(32) DEFAULT NULL,
  `userid` varchar(32) NOT NULL,
  `userlevel` tinyint UNSIGNED DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gali_skelbti` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`, `userid`, `userlevel`, `email`, `timestamp`, `gali_skelbti`) VALUES
('Administratorius', '16c354b68848cdbd8f54a226a0a55b21', 'a2fe399900de341c39c632244eaf8483', 9, 'demo@ktu.lt', '2026-11-04 22:06:14', 1),
('rimas', 'c2acd92812ef99acd3dcdbb746b9a434', '689e5b2971577d707becb97405ede951', 9, 'rimas@litnet.lt', '2024-07-02 20:49:33', 0),
('jonas', '64067822105b320085d18e386f57d89a', '9c5ddd54107734f7d18335a5245c286b', 255, 'rimas@litnet.lt', '2017-05-09 17:10:37', 0),
('pranas', '16c354b68848cdbd8f54a226a0a55b21', 'aa69001f0ba493bed7dddfd1cdb06591', 4, 'pranas@ltu.ee', '2024-07-02 18:48:36', 0),
('kostas', '1c37511487d38c3ebc4c59650ce2d65a', '69986045e0925262d43addddaf76094f', 5, 'eeee@ll.lt', '2018-02-16 16:04:35', 0),
('stud', 'c2acd92812ef99acd3dcdbb746b9a434', 'de2cde3560412df8be3d2773e9f1a8b3', 4, 'stud@ktu.lt', '2026-11-04 22:17:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `zinute`
--

CREATE TABLE `zinute` (
  `zinute_id` int UNSIGNED NOT NULL,
  `skelbimas_id` int UNSIGNED NOT NULL,
  `siuntejas_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gavejas_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tekstas` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `issiusta_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `perskaityta_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zinute`
--

INSERT INTO `zinute` (`zinute_id`, `skelbimas_id`, `siuntejas_id`, `gavejas_id`, `tekstas`, `issiusta_at`, `perskaityta_at`) VALUES
(1, 3, 'a2fe399900de341c39c632244eaf8483', 'de2cde3560412df8be3d2773e9f1a8b3', 'labas ar su raudonu antkakliu?', '2026-11-04 22:05:51', NULL),
(2, 3, 'de2cde3560412df8be3d2773e9f1a8b3', 'a2fe399900de341c39c632244eaf8483', 'labas, taip', '2026-11-04 22:06:10', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nuotrauka`
--
ALTER TABLE `nuotrauka`
  ADD PRIMARY KEY (`nuotrauka_id`),
  ADD KEY `skelbimas_id` (`skelbimas_id`);

--
-- Indexes for table `perziura`
--
ALTER TABLE `perziura`
  ADD PRIMARY KEY (`perziura_id`),
  ADD KEY `idx_skelbimas` (`skelbimas_id`);

--
-- Indexes for table `skelbimas`
--
ALTER TABLE `skelbimas`
  ADD PRIMARY KEY (`skelbimas_id`),
  ADD KEY `idx_autorius` (`autorius_id`),
  ADD KEY `idx_busena` (`busena`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`);

--
-- Indexes for table `zinute`
--
ALTER TABLE `zinute`
  ADD PRIMARY KEY (`zinute_id`),
  ADD KEY `idx_siuntejas` (`siuntejas_id`),
  ADD KEY `idx_gavejas` (`gavejas_id`),
  ADD KEY `skelbimas_id` (`skelbimas_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nuotrauka`
--
ALTER TABLE `nuotrauka`
  MODIFY `nuotrauka_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `perziura`
--
ALTER TABLE `perziura`
  MODIFY `perziura_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `skelbimas`
--
ALTER TABLE `skelbimas`
  MODIFY `skelbimas_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zinute`
--
ALTER TABLE `zinute`
  MODIFY `zinute_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nuotrauka`
--
ALTER TABLE `nuotrauka`
  ADD CONSTRAINT `nuotrauka_ibfk_1` FOREIGN KEY (`skelbimas_id`) REFERENCES `skelbimas` (`skelbimas_id`) ON DELETE CASCADE;

--
-- Constraints for table `perziura`
--
ALTER TABLE `perziura`
  ADD CONSTRAINT `perziura_ibfk_1` FOREIGN KEY (`skelbimas_id`) REFERENCES `skelbimas` (`skelbimas_id`) ON DELETE CASCADE;

--
-- Constraints for table `zinute`
--
ALTER TABLE `zinute`
  ADD CONSTRAINT `zinute_ibfk_1` FOREIGN KEY (`skelbimas_id`) REFERENCES `skelbimas` (`skelbimas_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
