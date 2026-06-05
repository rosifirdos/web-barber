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
    total_poin INT DEFAULT 0,
    tier ENUM('Bronze','Silver','Gold','Platinum') DEFAULT 'Bronze',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABEL: poin_history
-- Mencatat transaksi perolehan dan penukaran poin
-- ============================================
CREATE TABLE IF NOT EXISTS poin_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    booking_id INT DEFAULT NULL,
    jenis ENUM('Dapat','Redeem') NOT NULL,
    jumlah INT NOT NULL,
    saldo_akhir INT NOT NULL DEFAULT 0,
    keterangan VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES member(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE SET NULL,
    INDEX idx_member (member_id),
    INDEX idx_jenis (jenis)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: rewards
-- Katalog hadiah/benefit untuk member
-- ============================================
CREATE TABLE IF NOT EXISTS rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    poin_diperlukan INT NOT NULL,
    jenis ENUM('Diskon','Layanan Gratis','Voucher') DEFAULT 'Diskon',
    nilai_diskon DECIMAL(5,2) DEFAULT NULL,
    layanan_id INT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'gift',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABEL: reward_claims
-- Riwayat klaim dan voucher member
-- ============================================
CREATE TABLE IF NOT EXISTS reward_claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    reward_id INT NOT NULL,
    poin_digunakan INT NOT NULL,
    status ENUM('Aktif','Terpakai','Kadaluarsa') DEFAULT 'Aktif',
    kode_voucher VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expired_at DATETIME DEFAULT NULL,
    used_at DATETIME DEFAULT NULL,
    FOREIGN KEY (member_id) REFERENCES member(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE RESTRICT,
    INDEX idx_member (member_id),
    INDEX idx_status (status),
    INDEX idx_kode (kode_voucher)
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
    waktu_expired DATETIME DEFAULT NULL,
    status ENUM('Pending', 'Pending Payment', 'Confirmed', 'Proses', 'Selesai', 'Batal', 'Expired') DEFAULT 'Pending',
    status_pembayaran ENUM('Belum Bayar', 'Sudah DP', 'Lunas') DEFAULT 'Belum Bayar',
    metode_pembayaran VARCHAR(50) DEFAULT NULL,
    member_id INT DEFAULT NULL,
    total_harga DECIMAL(10,2),
    jumlah_dp DECIMAL(10,2) DEFAULT 0,
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
('admin', '$2y$10$OWfEhpjlJljiGbt8Zmbkjuq/w.Pvxxd5o1O7/2UTDTGp/6CX087SO', 'Administrator IF Barber');

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
