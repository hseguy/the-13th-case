-- ============================================================
-- THE 13TH CASE — Comprehensive Database Schema
-- 1947 Blackwood City Detective Mystery
-- Suitable for execution in MySQL Workbench or phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS `the_13th_case`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `the_13th_case`;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. GAME SESSIONS TABLE
CREATE TABLE IF NOT EXISTS `game_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `current_stage` VARCHAR(50) DEFAULT 'intro',
    `score` INT DEFAULT 0,
    `mistakes` INT DEFAULT 0,
    `game_status` ENUM('active', 'completed', 'failed') DEFAULT 'active',
    `saved_data` LONGTEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. EVIDENCE REFERENCE TABLE
CREATE TABLE IF NOT EXISTS `evidence` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evidence_name` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `location` VARCHAR(50) NOT NULL,
    `importance` TINYINT DEFAULT 1 COMMENT '1=Low, 2=Medium, 3=High, 4=Critical'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PLAYER EVIDENCE TABLE
CREATE TABLE IF NOT EXISTS `player_evidence` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` INT NOT NULL,
    `evidence_id` INT NOT NULL,
    `discovered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `game_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`evidence_id`) REFERENCES `evidence`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_session_evidence` (`session_id`, `evidence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. SUSPECTS REFERENCE TABLE
CREATE TABLE IF NOT EXISTS `suspects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `occupation` VARCHAR(100) NOT NULL,
    `motive` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. PLAYER INTERACTIONS TABLE
CREATE TABLE IF NOT EXISTS `player_interactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` INT NOT NULL,
    `interaction_type` VARCHAR(50) NOT NULL,
    `interaction_data` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `game_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEED EVIDENCE DATA
INSERT INTO `evidence` (`id`, `evidence_name`, `description`, `location`, `importance`) VALUES
(1, 'Stopped Clock', 'The ornate grandfather clock stopped at exactly 11:13 PM, suggesting the time of death was deliberately staged.', 'Private Study', 4),
(2, 'Blood-Stained Letter', 'A partially burned letter with dark blood spots addressed to Richard\'s lawyer regarding revocations.', 'Private Study', 3),
(3, 'Missing Document Cabinet', 'An open, ransacked document cabinet where private audit ledgers were stored.', 'Private Study', 4),
(4, 'Broken Glass', 'Shattered crystal whiskey glass near the mahogany desk, signs of a sudden physical struggle.', 'Private Study', 1),
(5, 'Torn Photograph', 'A formal family portrait where Victor Graves\' figure has been violently cut and torn out.', 'Living Room', 2),
(6, 'Wine Glass with Residue', 'A vintage wine glass containing cloudy chemical sediment at the base.', 'Living Room', 2),
(7, 'Poison Bottle', 'A small bottle of arsenic hidden behind pantry cleaning supplies, suspiciously placed as a frame-up.', 'Kitchen', 3),
(8, 'Secret Passage Map', 'A hidden architectural blueprint found behind books showing hidden corridors connecting the basement and study.', 'Library', 4),
(9, 'Torn Diary Page', 'A handwritten diary excerpt: "V.G. cannot be trusted. He thinks I have not seen the ledger discrepancies."', 'Library', 3),
(10, 'Hidden Financial Records', 'Secret double-entry ledgers detailing systematic embezzlement of estate funds by Victor Graves.', 'Secret Basement', 4),
(11, 'Old Photograph', 'A 1941 contract signing ceremony photograph showing traced and forged signatures.', 'Secret Basement', 2),
(12, 'Modified Will', 'A recently drafted last will and testament redirecting executive authority of Blackwood holdings to Victor Graves.', 'Secret Basement', 4),
(13, 'Cigarette Remains', 'Rare custom Turkish tobacco cigarette butts found in the basement entrance ashtray, smoked solely by Graves.', 'Living Room', 2)
ON DUPLICATE KEY UPDATE `evidence_name` = VALUES(`evidence_name`);

-- SEED SUSPECTS DATA
INSERT INTO `suspects` (`id`, `name`, `occupation`, `motive`, `description`) VALUES
(1, 'Eleanor Blackwood', 'Victim\'s Wife', 'Inheritance & Marital Estrangement', 'Elegant and guarded. Her marriage to Richard had fractured, though she denies knowledge of his financial battles.'),
(2, 'Thomas Reed', 'Business Partner', 'Financial Disputes & Debt Pressure', 'Stressed shipping magnate who was co-owner of Blackwood Maritime Ventures. Frustrated by Richard\'s recent audits.'),
(3, 'Henry Collins', 'Head Butler', 'Blackmail & Past Transgressions', 'Served the Blackwoods for 22 years. Aware of every architectural secret and hidden passage within the manor.'),
(4, 'Margaret Hayes', 'Investigative Journalist', 'Revenge & Exposé Scoop', 'Fearless reporter tracking illegal municipal contracts. Was scheduled to receive confidential whistleblowing files.'),
(5, 'Victor Graves', 'Personal Lawyer', 'Concealment of Embezzlement & Altered Will', 'The victim\'s trusted attorney and confidant. Plotted the murder to prevent Richard from reporting his criminal fraud.')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
