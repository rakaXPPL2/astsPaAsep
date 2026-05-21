-- Create Database
CREATE DATABASE IF NOT EXISTS dashboard_guru;
USE dashboard_guru;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('teacher', 'student') NOT NULL,
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes Table
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tasks Table
CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description LONGTEXT NOT NULL,
    class_id INT NOT NULL,
    deadline_text VARCHAR(100) NOT NULL,
    lesson_hour INT NOT NULL,
    attachment_path VARCHAR(255),
    is_urgent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    teacher_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Submissions Table
CREATE TABLE IF NOT EXISTS submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    task_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('belum_mengerjakan', 'sedang_mengerjakan', 'selesai', 'telat') DEFAULT 'belum_mengerjakan',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    UNIQUE KEY unique_task_student (task_id, student_id)
);

-- Insert Sample Data
INSERT INTO classes (class_name) VALUES 
('X PPLG 1'),
('X PPLG 2'),
('XI PPLG 1');

-- Insert Sample Users (passwords: PLAIN TEXT untuk mudah diakses)
INSERT INTO users (username, password, full_name, role, class_id) VALUES 
('guru001', 'password123', 'Budi Santoso', 'teacher', NULL),
('siswa001', 'password123', 'Ahmad Rifki', 'student', 1),
('siswa002', 'password123', 'Siti Nurhaliza', 'student', 1),
('siswa003', 'password123', 'Raden Wijaya', 'student', 2),
('siswa004', 'password123', 'Maya Putri', 'student', 2),
('siswa005', 'password123', 'Eka Prasetya', 'student', 1),
('siswa006', 'password123', 'Lina Wijaksono', 'student', 2),
('siswa007', 'password123', 'Hendra Gunawan', 'student', 1);

-- Insert Sample Tasks
INSERT INTO tasks (title, description, class_id, deadline_text, lesson_hour, is_urgent, teacher_id) VALUES 
('Produktif - CSS Dasar', 'Kerjakan soal halaman 10-15 bab CSS Dasar. Selesaikan dengan baik dan rapi.', 1, 'Besok, 11:00', 1, TRUE, 1),
('Responsive Web Design', 'Buat website responsif menggunakan Grid dan Flexbox. Minimum 3 halaman.', 2, '2 Hari Lagi, 14:30', 2, FALSE, 1),
('JavaScript DOM Manipulation', 'Lengkapi soal interaktif tentang DOM. Upload file HTML dan JS terpisah.', 1, '3 Hari Lagi, 10:00', 3, FALSE, 1);

-- Insert Sample Submissions
INSERT INTO submissions (task_id, student_id, status) VALUES 
(1, 1, 'selesai'),
(1, 2, 'sedang_mengerjakan'),
(1, 5, 'belum_mengerjakan'),
(2, 3, 'selesai'),
(2, 4, 'sedang_mengerjakan'),
(2, 6, 'telat'),
(3, 1, 'sedang_mengerjakan'),
(3, 5, 'belum_mengerjakan');
