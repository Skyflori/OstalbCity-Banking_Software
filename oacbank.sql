-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 14. Jun 2020 um 18:40
-- Server-Version: 10.3.15-MariaDB-1
-- PHP-Version: 7.3.4-2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `oacbank`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `accounts`
--

CREATE TABLE `accounts` (
  `ID` int(11) NOT NULL,
  `owner` tinytext NOT NULL,
  `type` enum('Betrieb','Betreuer','Gruppe Rathaus','Gruppe Polizei','Gruppe Radio','Gruppe Bank','Gruppe Gärtnerei','Gruppe Kunsthandwerk','Gruppe Fitness','Gruppe Schreinerei','Gruppe Zeitung','Gruppe Materiallager','Gruppe Gastro','Gruppe Beauty') NOT NULL,
  `balance` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `banned` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- Daten für Tabelle `accounts`
--

INSERT INTO `accounts` (`ID`, `owner`, `type`, `balance`, `banned`) VALUES
(1, 'Bank', 'Betrieb', 1539, 0),
(2, 'Rathaus & Arbeitsamt', 'Betrieb', 10, 0),
(3, 'Gastro', 'Betrieb', 1156, 0),
(51, 'FJ', 'Betreuer', 48, 0),
(52, 'LA', 'Betreuer', 30, 0),
(53, 'LD', 'Betreuer', 0, 0),
(101, 'JA', 'Gruppe 1', 0, 0),
(102, 'MAM', 'Gruppe 2', 0, 0),
(103, 'LA', 'Gruppe 3', 0, 0),
(104, 'OA', 'Gruppe 4', 38, 0),
(105, 'SF', 'Gruppe 5', 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `config`
--

CREATE TABLE `config` (
  `key` varchar(64) NOT NULL,
  `beschreibung` text NOT NULL,
  `val` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `config`
--

INSERT INTO `config` (`key`, `beschreibung`, `val`) VALUES
('betreuer_pin', 'Betreuer Pin zur Legitimierung', 2486),
('cost_kontoauszug', 'Kosten für Kontoauszug', 1),
('kinder_pin', 'Kleine Sicherung des Systems für Kinder', 9876),
('kontogebuehr', 'tägliche Kosten für Konten von Betrieben', 15),
('lohn', 'Lohn der Kinder', 10),
('presse_pin', 'Pin für die Presse zum Kontoauszugsdruck', 9000),
('transaction_limit', 'Überweisungslimit ohne Betreuercode', 20),
('withdraw_limit', 'Limit ohne Betreuer Code', 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log`
--

CREATE TABLE `log` (
  `ID` int(11) NOT NULL,
  `typ` enum('Einzahlung','Auszahlung','Transaktion','Sperren','Entsperren','Anmeldung','Abmeldung','Druckansicht') NOT NULL,
  `von` int(11) NOT NULL,
  `an` int(11) NOT NULL,
  `betrag` int(11) NOT NULL,
  `verwendungszweck` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- Daten für Tabelle `log`
--

INSERT INTO `log` (`ID`, `typ`, `von`, `an`, `betrag`, `verwendungszweck`, `time`) VALUES
(10, 'Druckansicht', 1, 0, 1, 'Der Kontoauszug für das Konto wurde für den Druck vorbereitet.', '2019-07-29 06:50:51'),
(11, 'Auszahlung', 52, 0, 5, 'Von dem Konto wurde Geld abgehoben.', '2019-07-29 08:09:38'),
(12, 'Einzahlung', 0, 52, 5, 'Auf das Konto wurde Geld einbezahlt.', '2019-07-29 08:10:23'),
(13, 'Transaktion', 1, 4, 3, 'Bezahlung Kiste', '2019-07-29 08:12:49'),
(14, 'Transaktion', 4, 1, 3, 'Bezahlung Tür', '2019-07-29 08:13:23'),
(1576, 'Einzahlung', 0, 4, 1000, 'Auf das Konto wurde Geld einbezahlt.', '2019-08-09 14:12:24');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`ID`);

--
-- Indizes für die Tabelle `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`key`);

--
-- Indizes für die Tabelle `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `accounts`
--
ALTER TABLE `accounts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;
--
-- AUTO_INCREMENT für Tabelle `log`
--
ALTER TABLE `log`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1577;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
