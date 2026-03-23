-- ============================================================
-- IAI DOCS — Relational Database Schema (v2)
-- Migrated from flat-string documents table to normalized form.
-- Run via backend/setup_db.php or directly in MySQL.
-- ============================================================

CREATE DATABASE IF NOT EXISTS iai_docs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE iai_docs;

-- ============================================================
-- 1. USERS — Authentication & Roles
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. YEARS — Academic years (managed by admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. LEVELS — L1, L2, L3 GLSI, L3 ASR
-- ============================================================
CREATE TABLE IF NOT EXISTS levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================
-- 4. SEMESTERS — Linked to a level
-- ============================================================
CREATE TABLE IF NOT EXISTS semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE,
    UNIQUE KEY uq_level_semester (level_id, name)
) ENGINE=InnoDB;

-- ============================================================
-- 5. SUBJECTS — Linked to a semester, toggleable
-- ============================================================
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    semester_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
    UNIQUE KEY uq_subject_semester (name, semester_id)
) ENGINE=InnoDB;

-- ============================================================
-- 6. DOCUMENT_TYPES — devoir, partiel, corrigé, cours, td/tp
-- ============================================================
CREATE TABLE IF NOT EXISTS document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================
-- 7. DOCUMENTS — The core table, fully relational
--    file_path stores a RELATIVE public URL, e.g.:
--    "L1/semestre1/algorithmique/partiel/2024.html"
--    NEVER an absolute filesystem path.
-- ============================================================
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    user_id INT,
    subject_id INT NOT NULL,
    type_id INT NOT NULL,
    year_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) DEFAULT NULL COMMENT 'Relative public URL to generated HTML, e.g. L1/semestre1/algo/partiel/2024.html',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES document_types(id) ON DELETE CASCADE,
    FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. NOTIFICATIONS — System alerts for users
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_id INT,
    type VARCHAR(50),
    title VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- SEED DATA — Initial reference values
-- ============================================================

-- Default Admin (password: admin123 — hashed with PASSWORD_DEFAULT in PHP)
INSERT IGNORE INTO users (name, email, password, role) 
VALUES ('Administrateur', 'admin@iai-docs.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Seed Years
INSERT IGNORE INTO years (year) VALUES (2024), (2025), (2026);

-- Seed Levels
INSERT IGNORE INTO levels (name) VALUES ('L1'), ('L2'), ('L3 GLSI'), ('L3 ASR');

-- Seed Semesters (2 per level)
INSERT IGNORE INTO semesters (level_id, name)
SELECT l.id, s.name
FROM levels l
CROSS JOIN (SELECT 'Semestre 1' AS name UNION SELECT 'Semestre 2') s
WHERE l.name IN ('L1', 'L2')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT IGNORE INTO semesters (level_id, name)
SELECT l.id, s.name
FROM levels l
CROSS JOIN (SELECT 'Semestre 5' AS name UNION SELECT 'Semestre 6') s
WHERE l.name IN ('L3 GLSI', 'L3 ASR')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT IGNORE INTO semesters (level_id, name)
SELECT l.id, s.name
FROM levels l
CROSS JOIN (SELECT 'Semestre 3' AS name UNION SELECT 'Semestre 4') s
WHERE l.name = 'L2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Seed Document Types
INSERT IGNORE INTO document_types (name) VALUES
    ('devoir'),
    ('partiel'),
    ('corrige'),
    ('cours'),
    ('td_tp');
