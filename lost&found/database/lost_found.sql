-- Smart Campus Lost and Found Management System Database Script
-- Database: lost_found

CREATE DATABASE IF NOT EXISTS `lost_found` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lost_found`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `admin`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pre-populate admin credentials (username: admin, password: admin123)
INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$12$MmYkls1cy8I/FMu67PImFeCOGp76mayXNlYOCzfxhE1l14gnqfgZy')
ON DUPLICATE KEY UPDATE `username`='admin';

-- --------------------------------------------------------
-- Table structure for table `lost_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lost_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `item_name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `lost_date` DATE NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Lost', 'Found', 'Claimed') DEFAULT 'Lost',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `found_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `found_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `item_name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `found_date` DATE NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Found', 'Claimed') DEFAULT 'Found',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `claims`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `claims` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `found_item_id` INT NOT NULL,
  `claim_reason` TEXT NOT NULL,
  `additional_info` TEXT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`found_item_id`) REFERENCES `found_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
