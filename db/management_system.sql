-- =====================================================
--  Bus Management System - Database Schema & Sample Data
--  Author: Shivam Rathore
--  Database: management_system
-- =====================================================

-- -----------------------------------------------------
-- Table: admins
-- -----------------------------------------------------
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Data
INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$xiy5ggXHGoAsfdm2rkqcKOYV70vt7YoTIImv/9rJMGzLdmUYggVka', '2025-09-03 09:06:36');


-- -----------------------------------------------------
-- Table: users
-- -----------------------------------------------------
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `unique_id` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(15) NOT NULL UNIQUE,
  `balance` FLOAT DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Data
INSERT INTO `users` (`id`, `unique_id`, `name`, `email`, `phone`, `balance`, `created_at`) VALUES
(1, 'USR9AC32CE1', 'Shiu Rathore', 'shiu@gmail.com', '1234567890', 3000, '2025-09-06 10:34:16'),
(2, 'USRA7D5A3A6', 'Rohan Rathore', 'rohanrathore@gmail.com', '35472163872', 7000, '2025-09-08 11:21:02');


-- -----------------------------------------------------
-- Table: journeys
-- -----------------------------------------------------
CREATE TABLE `journeys` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `start_lat` DECIMAL(10,6) DEFAULT NULL,
  `start_lng` DECIMAL(10,6) DEFAULT NULL,
  `end_lat` DECIMAL(10,6) DEFAULT NULL,
  `end_lng` DECIMAL(10,6) DEFAULT NULL,
  `distance_km` FLOAT DEFAULT 0,
  `fare` FLOAT DEFAULT 0,
  `status` ENUM('ongoing','completed') DEFAULT 'ongoing',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table: recharges
-- -----------------------------------------------------
CREATE TABLE `recharges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `amount` FLOAT NOT NULL,
  `admin_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Data
INSERT INTO `recharges` (`id`, `user_id`, `amount`, `admin_id`, `created_at`) VALUES
(9, 30, 500, 4, '2025-09-06 12:09:51'),
(10, 30, 500, 4, '2025-09-06 12:10:31'),
(11, 48, 5000, 4, '2025-09-08 11:26:58');
