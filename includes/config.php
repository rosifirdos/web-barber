<?php
/**
 * IF Barber — Konfigurasi Utama
 * Koneksi database dan konstanta aplikasi
 */

// ============================================
// SESSION
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// KONSTANTA APLIKASI
// ============================================
define('APP_NAME', 'IF Barber');
define('APP_TAGLINE', 'Premium Grooming Experience');
define('APP_VERSION', '1.0.0');

// URL & Path
define('BASE_URL', 'http://localhost/web_barber');
define('BASE_PATH', dirname(__DIR__));

// Jam Operasional
define('JAM_BUKA', '09:00');
define('JAM_TUTUP', '21:00');
define('SLOT_INTERVAL', 30); // menit per slot

// Hari Operasional (0=Minggu, 1=Senin, ..., 6=Sabtu)
define('HARI_LIBUR', [0]); // Libur hari Minggu

// ============================================
// KONEKSI DATABASE (XAMPP)
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'if_barber');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:monospace;padding:2rem;color:#ff6b6b;background:#1a1a2e;text-align:center;">
        <h2>⚠️ Koneksi Database Gagal</h2>
        <p>' . $conn->connect_error . '</p>
        <p style="color:#888;">Pastikan XAMPP MySQL sudah berjalan dan database <code>if_barber</code> sudah dibuat.</p>
    </div>');
}

$conn->set_charset('utf8mb4');

// ============================================
// TIMEZONE
// ============================================
date_default_timezone_set('Asia/Jakarta');

// ============================================
// GEMINI API (untuk Chatbot — Sesi 6)
// ============================================
define('GEMINI_API_KEY', ''); // Isi API key di sini nanti
define('GEMINI_MODEL', 'gemini-2.0-flash');
