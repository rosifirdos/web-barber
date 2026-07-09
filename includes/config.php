<?php
/**
 * IF Barber — Konfigurasi Utama
 * Koneksi database dan konstanta aplikasi
 */

// ============================================
// SESSION (Hardened)
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    // Uncomment baris berikut jika menggunakan HTTPS:
    // ini_set('session.cookie_secure', 1);
    session_start();
}
ob_start();

// ============================================
// SECURITY HEADERS
// ============================================
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ============================================
// CSRF TOKEN AUTO-GENERATE
// ============================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================
// KONSTANTA APLIKASI
// ============================================
define('APP_NAME', 'IF Barber');
define('APP_TAGLINE', 'Premium Grooming Experience');
define('APP_VERSION', '1.0.0');

// URL & Path
define('BASE_PATH', dirname(__DIR__));

// ============================================
// LOAD ENVIRONMENT VARIABLES (.env)
// ============================================
if (file_exists(BASE_PATH . '/.env')) {
    $envLines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            $val = trim($val, '"\'');
            if (!empty($key)) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/web_barber');

// Jam Operasional
define('JAM_BUKA', '09:00');
define('JAM_TUTUP', '21:00');
define('SLOT_INTERVAL', 30); // menit per slot

// Hari Operasional (0=Minggu, 1=Senin, ..., 6=Sabtu)
define('HARI_LIBUR', [0]); // Libur hari Minggu

// Membership Tier (berdasarkan total poin sepanjang waktu)
define('TIER_SILVER_MIN', 200);
define('TIER_GOLD_MIN', 500);
define('TIER_PLATINUM_MIN', 1000);

// Poin: 1 poin per Rp 1.000
define('POIN_PER_RUPIAH', 1000);

// ============================================
// KONEKSI DATABASE (XAMPP)
// ============================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'if_barber');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log('IF Barber DB Error: ' . $conn->connect_error);
    die('<div style="font-family:monospace;padding:2rem;color:#ff6b6b;background:#1a1a2e;text-align:center;">
        <h2>⚠️ Koneksi Database Gagal</h2>
        <p>Terjadi kesalahan koneksi database. Silakan coba lagi nanti.</p>
        <p style="color:#888;">Pastikan XAMPP MySQL sudah berjalan.</p>
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
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: 'gemini-2.0-flash');
