-- =========================================================
-- IT Helpdesk Notification System Database Schema
-- Database Name: smart_helpdesk
-- Compatible with: MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+ (InfinityFree / XAMPP / Hosting)
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- 1. ตาราง: users (ผู้ใช้งานในระบบ: User, Technician, Admin)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('User', 'Technician', 'Admin') NOT NULL DEFAULT 'User',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. ตาราง: categories (หมวดหมู่อุปกรณ์ / ปัญหา)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. ตาราง: tickets (ตั๋วแจ้งซ่อม)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `technician_id` INT(11) NULL DEFAULT NULL,
  `category_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `priority` ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
  `status` ENUM('Open', 'Assigned', 'InProgress', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
  `image` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ภาพถ่ายแจ้งซ่อมจากผู้ใช้',
  `repair_image` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ภาพผลงานหลังซ่อมเสร็จจากช่าง',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` DATETIME NULL DEFAULT NULL,
  `closed_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tickets_user_id` (`user_id`),
  INDEX `idx_tickets_technician_id` (`technician_id`),
  INDEX `idx_tickets_category_id` (`category_id`),
  INDEX `idx_tickets_status` (`status`),
  CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tickets_tech` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tickets_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. ตาราง: comments (ประวัติการซ่อม / ข้อความสนทนา)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `body` TEXT NOT NULL,
  `image_path` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_comments_ticket_id` (`ticket_id`),
  INDEX `idx_comments_user_id` (`user_id`),
  CONSTRAINT `fk_comments_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. ตาราง: status_logs (บันทึก Timeline การเปลี่ยนสถานะตาม Sequence Diagram 6.2)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `status_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` INT(11) NOT NULL,
  `changed_by` INT(11) NOT NULL,
  `from_status` VARCHAR(50) NOT NULL,
  `to_status` VARCHAR(50) NOT NULL,
  `note` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status_logs_ticket` (`ticket_id`),
  INDEX `idx_status_logs_user` (`changed_by`),
  CONSTRAINT `fk_logs_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. ตาราง: ratings (ประเมินคะแนนความพึงพอใจ 1-5 ดาว)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` INT(11) NOT NULL UNIQUE,
  `score` TINYINT(1) NOT NULL CHECK (`score` BETWEEN 1 AND 5),
  `feedback` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ratings_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- ข้อมูลเริ่มต้นระบบ (SEED DATA)
-- =========================================================

-- 1. บัญชีผู้ใช้งานเริ่มต้น 3 Roles (รหัสผ่าน: password123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'สมชาย รักงาน (User)', 'user@helpdesk.com', 'password123', 'User'),
(2, 'ช่างวิชัย (Tech)', 'tech@helpdesk.com', 'password123', 'Technician'),
(3, 'ผู้ดูแลระบบ (Admin)', 'admin@helpdesk.com', 'password123', 'Admin'),
(4, 'ช่างสมเกียรติ (Tech)', 'tech2@helpdesk.com', 'password123', 'Technician')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. หมวดหมู่อุปกรณ์
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Hardware', 'อุปกรณ์ชำรุด เช่น หน้าจอ, คีย์บอร์ด, เมนบอร์ด'),
(2, 'Network', 'ปัญหาอินเทอร์เน็ต, สาย LAN, Wi-Fi'),
(3, 'Printer', 'เครื่องพิมพ์กระดาษติด, หมึกหมด, สแกนเนอร์'),
(4, 'Software', 'โปรแกรมใช้งาน, วินโดวส์, ไวรัส')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 3. ตั๋วเริ่มต้นสำหรับทดสอบระบบ
INSERT INTO `tickets` (`id`, `user_id`, `technician_id`, `category_id`, `title`, `description`, `priority`, `status`, `created_at`, `updated_at`) VALUES
(101, 1, NULL, 1, 'คอมพิวเตอร์เปิดไม่ติด มีไฟกระพริบสีส้ม', 'กดสวิตช์หลังเครื่องแล้วไม่มีเสียงพัดลมหมุน ไฟขึ้นสีส้มกระพริบ', 'High', 'Open', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(102, 1, 2, 3, 'เครื่องพิมพ์กระดาษติด ชั้น 3 ฝ่ายการเงิน', 'สั่งพิมพ์แล้วกระดาษติดแน่น ดึงไม่ออก', 'Medium', 'InProgress', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 30 MINUTE))
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- 4. ข้อความบันทึกการซ่อมเริ่มต้น
INSERT INTO `comments` (`ticket_id`, `user_id`, `body`, `created_at`) VALUES
(101, 1, 'ลองกดสวิตช์หลังเครื่องแล้วยังไม่ติดครับ', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(102, 2, 'กำลังรื้อดึงเศษกระดาษที่ติดลูกกลิ้งครับ', DATE_SUB(NOW(), INTERVAL 30 MINUTE));

-- 5. บันทึกสถานะเริ่มต้น
INSERT INTO `status_logs` (`ticket_id`, `changed_by`, `from_status`, `to_status`, `note`, `created_at`) VALUES
(101, 1, 'None', 'Open', 'เปิดตั๋วแจ้งซ่อมใหม่', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(102, 1, 'None', 'Open', 'เปิดตั๋วแจ้งซ่อมใหม่', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(102, 3, 'Open', 'Assigned', 'มอบหมายงานให้ช่างวิชัย', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(102, 2, 'Assigned', 'InProgress', 'เริ่มดำเนินการตรวจสอบหน้างาน', DATE_SUB(NOW(), INTERVAL 30 MINUTE));

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
