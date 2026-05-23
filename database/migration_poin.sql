-- ============================================
-- IF Barber — Migration: Sistem Poin & Benefit
-- Jalankan script ini di phpMyAdmin atau MySQL CLI
-- ============================================

USE if_barber;

-- ============================================
-- 1. Tambah kolom poin di tabel member
-- ============================================
ALTER TABLE member
    ADD COLUMN total_poin INT DEFAULT 0 AFTER password,
    ADD COLUMN tier ENUM('Bronze','Silver','Gold','Platinum') DEFAULT 'Bronze' AFTER total_poin;

-- ============================================
-- 2. Tabel poin_history
-- Mencatat setiap transaksi poin (dapat / redeem)
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
-- 3. Tabel rewards
-- Daftar reward yang bisa ditukar member
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
-- 4. Tabel reward_claims
-- Riwayat klaim reward oleh member
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
-- 5. Seed Data: Rewards
-- ============================================
INSERT INTO rewards (nama, deskripsi, poin_diperlukan, jenis, nilai_diskon, layanan_id, icon) VALUES
('Diskon 10%', 'Potongan 10% untuk booking layanan apa saja. Berlaku 30 hari.', 100, 'Diskon', 10.00, NULL, 'percent'),
('Diskon 20%', 'Potongan 20% untuk booking layanan apa saja. Berlaku 30 hari.', 200, 'Diskon', 20.00, NULL, 'percent'),
('Free Shaving', 'Gratis layanan Shaving (senilai Rp 25.000). Berlaku 30 hari.', 150, 'Layanan Gratis', NULL, 3, 'scissors'),
('Free Regular Haircut', 'Gratis layanan Regular Haircut (senilai Rp 35.000). Berlaku 30 hari.', 250, 'Layanan Gratis', NULL, 1, 'scissors'),
('Free Premium Haircut', 'Gratis layanan Premium Haircut (senilai Rp 55.000). Berlaku 30 hari.', 400, 'Layanan Gratis', NULL, 2, 'crown'),
('Free Hair Treatment', 'Gratis layanan Hair Treatment (senilai Rp 75.000). Berlaku 30 hari.', 500, 'Layanan Gratis', NULL, 8, 'sparkles');
