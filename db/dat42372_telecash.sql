-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 19, 2019 alle 16:57
-- Versione del server: 10.1.36-MariaDB
-- Versione PHP: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dat42372_telecash`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `coupon_queue`
--

CREATE TABLE `coupon_queue` (
  `id` bigint(20) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `last_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `password` varchar(256) COLLATE utf8_bin NOT NULL,
  `merchant_code` varchar(16) COLLATE utf8_bin NOT NULL,
  `phone_service` varchar(32) COLLATE utf8_bin NOT NULL,
  `coupon_code` varchar(256) COLLATE utf8_bin NOT NULL,
  `coupon_type` varchar(2) COLLATE utf8_bin NOT NULL,
  `coupon_value` varchar(32) COLLATE utf8_bin NOT NULL,
  `coupon_channel` varchar(64) COLLATE utf8_bin NOT NULL,
  `coupon_custom` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `start_date` varchar(64) COLLATE utf8_bin NOT NULL,
  `start_time` varchar(64) COLLATE utf8_bin NOT NULL,
  `stop_date` varchar(64) COLLATE utf8_bin NOT NULL,
  `stop_time` varchar(64) COLLATE utf8_bin NOT NULL,
  `customer_limit` varchar(64) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dump dei dati per la tabella `coupon_queue`
--

INSERT INTO `coupon_queue` (`id`, `status`, `last_action`, `username`, `password`, `merchant_code`, `phone_service`, `coupon_code`, `coupon_type`, `coupon_value`, `coupon_channel`, `coupon_custom`, `start_date`, `start_time`, `stop_date`, `stop_time`, `customer_limit`) VALUES
(1482, -1, '2019-03-14 09:08:18', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380', '100759', '0516808266', '3471433426', '1', '1', '1', '393471433426', '07/03/2019', '00:00:00', '07/03/2019', '23:59:00', 'latua'),
(1483, 2, '2019-03-17 10:37:50', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380', '100759', '0516808266', '3755282104', '1', '1', '1', '393755282104', '16/03/2019', '00:00:00', '07/03/2019', '23:59:00', 'latua'),
(1484, -14, '2019-04-19 09:01:13', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380', '100759', '0516808266', '3805926275', '1', '1', '1', '393805926275', '09/03/2019', '00:00:00', '07/03/2019', '23:59:00', 'latua'),
(1485, 0, '2019-03-17 10:37:17', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380', '100759', '0516808266', '3896512499', '1', '1', '1', '393896512499', '11/03/2019', '00:00:00', '07/03/2019', '23:59:00', 'latua'),
(1486, 99, '2019-03-17 10:36:45', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380', '100759', '0516808266', '3471433427', '1', '1', '1', '393471433426', '10/03/2019', '00:00:00', '07/03/2019', '23:59:00', 'latua'),
(1487, -1, '2019-03-14 09:08:18', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380', '100759', '0516808266', '3471433426', '1', '1', '1', '393471433426', '27/02/2019', '00:00:00', '07/03/2019', '23:59:00', 'latua'),
(1488, -6, '2019-04-19 09:35:59', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380', '100759', '0516808266', '123', '1', '', '1', '39123', '03/04/2019', '00:00:00', '03/04/2019', '23:59:00', 'latua');

-- --------------------------------------------------------

--
-- Struttura della tabella `retailers`
--

CREATE TABLE `retailers` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `nomevisualizzato` varchar(100) NOT NULL,
  `user_type` varchar(100) NOT NULL DEFAULT '0',
  `utente_DB_Telecash` varchar(128) NOT NULL,
  `password_DB_Telecash` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `retailers`
--

INSERT INTO `retailers` (`id`, `status`, `username`, `password`, `nomevisualizzato`, `user_type`, `utente_DB_Telecash`, `password_DB_Telecash`) VALUES
(38, 1, 'vitaliano', 'f06e26166e000a16ba0a9c8ae4a4f61a', 'Vitaliano Palmarini', 'admin', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380'),
(39, 1, 'daniele', 'f06e26166e000a16ba0a9c8ae4a4f61a', 'Daniele Mattioli', 'admin', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380'),
(40, 1, 'admin', 'f06e26166e000a16ba0a9c8ae4a4f61a', 'Amministratore', 'admin', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380'),
(41, 1, 'servi', 'c87a0efbdf7c69e09b001fd8c01c7ba4', 'Servizi Premium', 'retailer', 'servi', '2e34054fed5509ff10ff081e147af5b485d35943061f51e670d961fdc67d61b371cdd1ed0bd51b7f52b7b0c369d24b02b797ee3cf5dd583257efb07f79730380'),
(42, 1, 'telecash', '85bd62f6347d7b1eedf5e73db676c3a3', 'telecash', 'retailer', 'telecash', 'PPokJhnbGtrffT56YhhgfRtxzmkga'),
(49, 1, 'sandbox', '93bc63e0b4f48fbbff568d9fc0dc3def', 'Sandbox', 'retailer', 'servi', 'f1100e9f3c60ca99295d77759e307748dc9b30e82f461168b474fc4addbde211c8389763f943d438ca74ffb9f8f87e5c920f996b55d5d901d006044e2da33ac9'),
(51, 1, 'sandbox2', 'be513390f976b2cc5b5d84cded20ea52', 'sanche', 'retailer', '55555', '4444');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `retailer` int(11) NOT NULL DEFAULT '0',
  `nomevisualizzato` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `status`, `username`, `password`, `retailer`, `nomevisualizzato`) VALUES
(10, 1, 'anna5', '60a751c45a93e77b74b9de96edc98795', 41, 'PASQUALIN ANNAMARIA'),
(11, 1, 'lua1', '5c8c75212a60ae873e2655a0393a1aa2', 41, 'LUANA PORFIDIA'),
(12, 1, 'latua', 'ce08fabd169d67b770a8464f541676c6', 41, 'LA TUA CARTOMANTE'),
(13, 1, 'tv', 'c9a1fdac6e082dd89e7173244f34d7b3', 41, 'CARTOTV'),
(14, 1, 'nuovi', '06de21cc82e50066f7817124a0642399', 41, 'nuovi codici'),
(15, 1, 'ric', '77c345c88d5abc96dff43b05f067805d', 42, 'GEA S.R.L.'),
(16, 1, 'elena12', 'cd7e5e3c319c582e8fa815026eddcfb0', 41, 'ELENA PAGANO'),
(17, 1, 'wbcdc', '1b164eb54a2ab4e540419801668804a5', 42, 'WEBNOW S.R.L.'),
(18, 1, 'webvision', 'd2df1acccfc1234c81b3a746f011ed4d', 42, 'WEB VISION S.R.L.'),
(24, 0, 'anna5', '60a751c45a93e77b74b9de96edc98795', 49, 'PASQUALIN ANNAMARIA'),
(25, 0, 'bnet', 'f7d6e0a88a5832b5761ca0255482c4d1', 49, 'Bauernet XXI S.L.');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `coupon_queue`
--
ALTER TABLE `coupon_queue`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`);

--
-- Indici per le tabelle `retailers`
--
ALTER TABLE `retailers`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Retailer` (`retailer`) USING BTREE;

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `coupon_queue`
--
ALTER TABLE `coupon_queue`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1489;

--
-- AUTO_INCREMENT per la tabella `retailers`
--
ALTER TABLE `retailers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_Retailer` FOREIGN KEY (`retailer`) REFERENCES `retailers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
