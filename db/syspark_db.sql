-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2025 at 04:32 AM
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
-- Database: `app`
--
CREATE DATABASE IF NOT EXISTS `app` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `app`;

-- --------------------------------------------------------

--
-- Table structure for table `parked_vehicles`
--

CREATE TABLE IF NOT EXISTS `parked_vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `parking_spot_id` int(11) NOT NULL,
  `date_start` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_end` timestamp NULL DEFAULT NULL,
  `value` decimal(19,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_parking_spot_uniqueness` (`vehicle_id`,`parking_spot_id`),
  KEY `fk_parked_vehicles_user_idx` (`user_id`),
  KEY `fk_vehicle_id_idx` (`vehicle_id`),
  KEY `fk_parking_spot_id_idx` (`parking_spot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `parked_vehicles`
--
DELIMITER $$
CREATE TRIGGER `trg_parked_vehicles_after_delete` AFTER DELETE ON `parked_vehicles` FOR EACH ROW BEGIN
    UPDATE parking_spots
       SET status = 'available',
           in_use_by = NULL
     WHERE id = OLD.parking_spot_id
       AND user_id = OLD.user_id;        -- NEW!
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_parked_vehicles_after_insert` AFTER INSERT ON `parked_vehicles` FOR EACH ROW BEGIN
  UPDATE parking_spots AS p
    JOIN vehicles      AS v ON v.id = NEW.vehicle_id
  SET
    p.status    = 'busy',
    p.in_use_by = v.plate
  WHERE p.id = NEW.parking_spot_id
    AND p.user_id = NEW.user_id;          -- NEW!
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_parked_vehicles_after_update` AFTER UPDATE ON `parked_vehicles` FOR EACH ROW BEGIN
  IF OLD.parking_spot_id <> NEW.parking_spot_id
     OR (OLD.date_end IS NULL AND NEW.date_end IS NOT NULL) THEN
    UPDATE parking_spots
       SET status = 'available',
           in_use_by = NULL
     WHERE id = OLD.parking_spot_id
       AND user_id = OLD.user_id;        -- NEW!
  END IF;

  IF NEW.date_end IS NULL THEN
    UPDATE parking_spots AS p
      JOIN vehicles      AS v ON v.id = NEW.vehicle_id
    SET p.status    = 'busy',
        p.in_use_by = v.plate
    WHERE p.id = NEW.parking_spot_id
      AND p.user_id = NEW.user_id;       -- NEW!
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `parking_spots`
--

CREATE TABLE IF NOT EXISTS `parking_spots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `spot_number` int(11) NOT NULL,
  `status` enum('available','busy','innoperant') NOT NULL DEFAULT 'available',
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `in_use_by` varchar(8) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_spot_unique` (`user_id`,`spot_number`),
  KEY `fk_parking_spots_user_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `parking_spots`
--
DELIMITER $$
CREATE TRIGGER `trg_parking_spots_before_update_inactive` BEFORE UPDATE ON `parking_spots` FOR EACH ROW BEGIN
  IF NEW.active = 0 THEN
    SET NEW.status = 'innoperant';
  ELSEIF NEW.active = 1 AND NEW.in_use_by IS NOT NULL THEN
    SET NEW.status = 'busy';
  ELSEIF NEW.active = 1 THEN
    SET NEW.status = 'available';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_parking_spots_set_number` BEFORE INSERT ON `parking_spots` FOR EACH ROW BEGIN
  DECLARE next_no INT;
  SELECT COALESCE(MAX(spot_number),0) + 1
    INTO next_no
    FROM parking_spots
   WHERE user_id = NEW.user_id;
  SET NEW.spot_number = next_no;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pricing_rules`
--

CREATE TABLE IF NOT EXISTS `pricing_rules` (
  `user_id` int(11) NOT NULL,
  `price_per_min` decimal(10,4) NOT NULL DEFAULT 0.3300,
  `fixed_fee` decimal(10,2) NOT NULL DEFAULT 15.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_UNIQUE` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plate` varchar(8) NOT NULL,
  `model` varchar(100) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp(),
  `color` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_plate_UNIQUE` (`user_id`,`plate`),
  KEY `fk_vehicles_user_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `parked_vehicles`
--
ALTER TABLE `parked_vehicles`
  ADD CONSTRAINT `fk_parked_vehicles_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_parking_spot_id` FOREIGN KEY (`parking_spot_id`) REFERENCES `parking_spots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicle_id` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD CONSTRAINT `fk_parking_spots_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pricing_rules`
--
ALTER TABLE `pricing_rules`
  ADD CONSTRAINT `fk_pricing_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
