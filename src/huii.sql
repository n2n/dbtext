-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               10.1.28-MariaDB - mariadb.org binary distribution
-- Server Betriebssystem:        Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
DROP TABLE IF EXISTS `dbtext_group`;
-- Exportiere Struktur von Tabelle n2n_rocket_playground.dbtext_group
CREATE TABLE IF NOT EXISTS `dbtext_group` (
  `namespace` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL
) ;
DROP TABLE IF EXISTS `dbtext_text`;
-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle n2n_rocket_playground.dbtext_text
CREATE TABLE IF NOT EXISTS `dbtext_text` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `key` varchar(255) NOT NULL,
  `group_namespace` varchar(255) DEFAULT NULL,
  `placeholders` varchar(1000) DEFAULT NULL
) ;
DROP TABLE IF EXISTS `dbtext_text_t`;
-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle n2n_rocket_playground.dbtext_text_t
CREATE TABLE `dbtext_text_t` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `n2n_locale` varchar(50) DEFAULT NULL,
  `str` varchar(8191) DEFAULT NULL,
  `text_id` int(10) DEFAULT NULL
) ;
-- Daten Export vom Benutzer nicht ausgewählt
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
