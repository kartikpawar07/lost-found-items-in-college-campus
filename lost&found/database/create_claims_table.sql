-- SQL Table Creation Script for Claims Module
-- Run this query in phpMyAdmin (or MySQL CLI) inside your active 'lost_found' database.

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
