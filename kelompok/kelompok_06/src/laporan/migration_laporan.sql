-- ========================================
-- DATABASE MODUL LAPORAN PENGADUAN
-- Anggota 3: Nabila Salwa
-- ========================================

-- Membuat tabel users untuk autentikasi
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index untuk mempercepat query users
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_role ON users(role);

-- Membuat tabel laporan
CREATE TABLE IF NOT EXISTS laporan (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    judul_laporan VARCHAR(255) NOT NULL,
    isi_laporan TEXT NOT NULL,
    status ENUM('pending', 'diproses', 'selesai', 'ditolak') DEFAULT 'pending',
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key ke tabel users
    CONSTRAINT fk_laporan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index untuk mempercepat query laporan
CREATE INDEX idx_user_id ON laporan(user_id);
CREATE INDEX idx_status ON laporan(status);
CREATE INDEX idx_tanggal ON laporan(tanggal);

-- ========================================
-- DATA DUMMY UNTUK TESTING (OPSIONAL)
-- ========================================
-- Uncomment jika ingin menambahkan data dummy untuk testing

-- Data dummy users
INSERT INTO users (username, nama, email, password, role) VALUES
('john_doe', 'John Doe', 'john@example.com', '$2y$10$abcdefghijklmnopqrstuv1234567890123456789012', 'user'),
('admin', 'Admin SILATIUM', 'admin@silatium.com', '$2y$10$abcdefghijklmnopqrstuv1234567890123456789012', 'admin');
-- Password untuk kedua user di atas adalah: password

-- Data dummy laporan
INSERT INTO laporan (user_id, judul_laporan, isi_laporan, status) VALUES
(1, 'Bus Koridor 1 Sering Terlambat di Terminal Rajabasa', 'Bus koridor 1 sering terlambat sampai 30 menit dari jadwal yang tertera...', 'pending'),
(1, 'AC Bus Tidak Berfungsi di Rute Tanjung Karang', 'AC di dalam bus tidak dingin sama sekali, membuat penumpang tidak nyaman...', 'diproses'),
(1, 'Kebersihan Bus Kurang Terjaga', 'Tempat duduk kotor dan berbau tidak sedap, perlu ditingkatkan kebersihan...', 'selesai');
