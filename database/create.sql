-- Adminer 4.8.1 MySQL 10.6.12-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `pid_pay_method`;
CREATE TABLE `pid_pay_method` (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `val` int(11) NOT NULL,
                                  `desc` varchar(255) NOT NULL,
                                  PRIMARY KEY (`id`),
                                  UNIQUE KEY `val` (`val`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `pid_point`;
CREATE TABLE `pid_point` (
                             `id` int(11) NOT NULL AUTO_INCREMENT,
                             `pid_id` varchar(20) NOT NULL,
                             `pid_point_type_id` int(11) NOT NULL,
                             `name` varchar(255) NOT NULL,
                             `address` varchar(255) NOT NULL,
                             `lat` decimal(15,13) NOT NULL,
                             `lon` decimal(15,13) NOT NULL,
                             `link` text DEFAULT NULL,
                             `remarks` text DEFAULT NULL,
                             PRIMARY KEY (`id`),
                             UNIQUE KEY `pid_id` (`pid_id`),
                             KEY `pid_point_type_id` (`pid_point_type_id`),
                             CONSTRAINT `pid_point_ibfk_1` FOREIGN KEY (`pid_point_type_id`) REFERENCES `pid_point_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `pid_point_opening_hours`;
CREATE TABLE `pid_point_opening_hours` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
                                           `pid_point_id` int(11) NOT NULL,
                                           `weekday` tinyint(4) NOT NULL,
                                           `start_hour` time NOT NULL,
                                           `end_hour` time NOT NULL,
                                           PRIMARY KEY (`id`),
                                           KEY `pid_point_id` (`pid_point_id`),
                                           CONSTRAINT `pid_point_opening_hours_ibfk_1` FOREIGN KEY (`pid_point_id`) REFERENCES `pid_point` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `pid_point_pay_method`;
CREATE TABLE `pid_point_pay_method` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `pid_point_id` int(11) NOT NULL,
                                        `pid_pay_method_id` int(11) NOT NULL,
                                        PRIMARY KEY (`id`),
                                        KEY `pid_point_id` (`pid_point_id`),
                                        KEY `pid_pay_method_id` (`pid_pay_method_id`),
                                        CONSTRAINT `pid_point_pay_method_ibfk_1` FOREIGN KEY (`pid_point_id`) REFERENCES `pid_point` (`id`) ON DELETE CASCADE,
                                        CONSTRAINT `pid_point_pay_method_ibfk_2` FOREIGN KEY (`pid_pay_method_id`) REFERENCES `pid_pay_method` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `pid_point_service`;
CREATE TABLE `pid_point_service` (
                                     `id` int(11) NOT NULL AUTO_INCREMENT,
                                     `pid_point_id` int(11) NOT NULL,
                                     `pid_service_id` int(11) NOT NULL,
                                     PRIMARY KEY (`id`),
                                     KEY `pid_point_id` (`pid_point_id`),
                                     KEY `pid_service_id` (`pid_service_id`),
                                     CONSTRAINT `pid_point_service_ibfk_1` FOREIGN KEY (`pid_point_id`) REFERENCES `pid_point` (`id`) ON DELETE CASCADE,
                                     CONSTRAINT `pid_point_service_ibfk_2` FOREIGN KEY (`pid_service_id`) REFERENCES `pid_service` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `pid_point_type`;
CREATE TABLE `pid_point_type` (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `name` varchar(100) NOT NULL,
                                  `desc` varchar(255) NOT NULL,
                                  PRIMARY KEY (`id`),
                                  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `pid_service`;
CREATE TABLE `pid_service` (
                               `id` int(11) NOT NULL AUTO_INCREMENT,
                               `val` int(11) NOT NULL,
                               `desc` varchar(255) NOT NULL,
                               `group` varchar(255) NOT NULL,
                               PRIMARY KEY (`id`),
                               UNIQUE KEY `val` (`val`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 2023-11-17 12:36:40