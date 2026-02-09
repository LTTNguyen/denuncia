-- Create table for email recipients (new report notifications)
-- Run this once in your DB (phpMyAdmin or mysql client):
--   USE denuncias_portal;
--   SOURCE db_notify_recipients.sql;

CREATE TABLE IF NOT EXISTS `portal_notify_recipient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `email` varchar(160) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_company_cat_email` (`company_id`,`category_id`,`email`),
  KEY `idx_company` (`company_id`),
  KEY `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Example data (edit emails)
-- company-wide recipients (category_id NULL): they receive all categories of that company
-- INSERT INTO portal_notify_recipient (company_id, category_id, email, name) VALUES
-- (3, NULL, 'boss.andes@empresa.com', 'Boss Andes');
--
-- category-specific recipients: they receive only that category for that company
-- INSERT INTO portal_notify_recipient (company_id, category_id, email, name) VALUES
-- (3, 23, 'prevencion@empresa.com', 'Prevención de Riesgos');
