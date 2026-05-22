-- ============================================
-- IF Barber — Database Schema & Seed Data
-- ============================================

CREATE DATABASE IF NOT EXISTS if_barber
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE if_barber;

-- ============================================
-- TABEL: layanan
-- Menyimpan daftar layanan barbershop
-- ============================================
CREATE TABLE IF NOT EXISTS layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL,
    durasi_menit INT DEFAULT 30,
    gambar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABEL: barber
-- Menyimpan data staff / tukang cukur
-- ============================================
CREATE TABLE IF NOT EXISTS barber (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    bio TEXT,
    spesialisasi VARCHAR(255),
    foto VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABEL: admin
-- Akun admin untuk mengelola dashboard
-- ============================================
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABEL: member
-- Pelanggan tetap yang terdaftar
-- ============================================
CREATE TABLE IF NOT EXISTS member (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABEL: booking
-- Reservasi / antrean pelanggan
-- ============================================
CREATE TABLE IF NOT EXISTS booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    layanan_id INT NOT NULL,
    barber_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    status ENUM('Pending','Proses','Selesai','Batal') DEFAULT 'Pending',
    member_id INT DEFAULT NULL,
    total_harga DECIMAL(10,2),
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON DELETE RESTRICT,
    FOREIGN KEY (barber_id) REFERENCES barber(id) ON DELETE RESTRICT,
    FOREIGN KEY (member_id) REFERENCES member(id) ON DELETE SET NULL,
    INDEX idx_tanggal (tanggal),
    INDEX idx_status (status),
    INDEX idx_barber_tanggal (barber_id, tanggal)
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA: Admin default
-- Password: admin123 (hashed with password_hash)
-- ============================================
INSERT INTO admin (username, password, nama) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator IF Barber');

-- ============================================
-- SEED DATA: Layanan barbershop
-- ============================================
INSERT INTO layanan (nama, deskripsi, harga, durasi_menit, is_active) VALUES
('Regular Haircut', 'Potong rambut standar dengan teknik klasik dan modern. Termasuk cuci rambut dan styling ringan.', 35000.00, 30, 1),
('Premium Haircut', 'Potong rambut premium dengan konsultasi gaya, cuci rambut, hair tonic, dan styling lengkap.', 55000.00, 45, 1),
('Shaving', 'Cukur bersih menggunakan pisau cukur profesional dengan hot towel treatment.', 25000.00, 20, 1),
('Beard Trim & Shape', 'Rapikan dan bentuk jenggot sesuai kontur wajah. Termasuk beard oil treatment.', 30000.00, 25, 1),
('Hair Coloring', 'Pewarnaan rambut dengan produk berkualitas. Tersedia berbagai pilihan warna trendy.', 150000.00, 90, 1),
('Fade Cut', 'Potongan fade modern (low, mid, high) dengan transisi halus dan presisi tinggi.', 45000.00, 40, 1),
('Kids Haircut', 'Potong rambut khusus anak-anak (di bawah 12 tahun) dengan suasana ramah anak.', 25000.00, 25, 1),
('Hair Treatment', 'Perawatan rambut intensif: creambath, hair spa, dan scalp massage untuk rambut sehat.', 75000.00, 60, 1);

-- ============================================
-- SEED DATA: Barber / Staff
-- ============================================
INSERT INTO barber (nama, bio, spesialisasi, is_active) VALUES
('Reza Mahendra', 'Barber profesional dengan pengalaman 8 tahun. Ahli dalam berbagai teknik modern dan klasik. Pernah mengikuti workshop di Bangkok dan Kuala Lumpur.', 'Fade Cut, Pompadour, Quiff, Modern Classic', 1),
('Dimas Pratama', 'Spesialis dalam seni mencukur dan grooming pria. Dikenal dengan ketelitian dan konsistensi hasil kerja yang premium.', 'Beard Sculpting, Hot Towel Shave, Skin Fade', 1),
('Arief Setiawan', 'Barber kreatif yang menguasai teknik hair art dan design. Selalu up-to-date dengan tren terbaru dari social media.', 'Hair Tattoo, Freehand Design, Textured Crop', 1),
('Bayu Aditya', 'Ahli pewarnaan rambut dan treatment. Memahami berbagai jenis rambut dan solusi perawatan yang tepat untuk setiap pelanggan.', 'Hair Coloring, Balayage, Hair Treatment, Perming', 1);

-- ============================================
-- SEED DATA: Booking contoh (opsional, untuk testing)
-- ============================================
INSERT INTO booking (nama_pelanggan, no_hp, layanan_id, barber_id, tanggal, jam, status, total_harga) VALUES
('Ahmad Fadli', '081234567890', 1, 1, CURDATE(), '09:00:00', 'Pending', 35000.00),
('Budi Santoso', '082345678901', 2, 2, CURDATE(), '10:00:00', 'Proses', 55000.00),
('Charlie Wijaya', '083456789012', 6, 3, CURDATE(), '11:00:00', 'Pending', 45000.00);
