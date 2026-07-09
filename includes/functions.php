<?php
/**
 * IF Barber — Helper Functions
 * Kumpulan fungsi utilitas yang digunakan di seluruh aplikasi
 */

/**
 * Sanitasi input untuk mencegah XSS
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate CSRF hidden input field
 * @return string HTML hidden input
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? '') . '">';
}

/**
 * Verifikasi CSRF token dari POST request
 * @return bool true jika valid
 */
function verifyCsrf() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Rate limiting sederhana berbasis session
 * @param string $key   Identifier unik (e.g., 'login_admin')
 * @param int $maxAttempts Maksimal percobaan
 * @param int $windowSeconds Jendela waktu (detik)
 * @return bool true jika masih diizinkan, false jika rate-limited
 */
function checkRateLimit($key, $maxAttempts = 5, $windowSeconds = 300) {
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 0, 'first_attempt' => time()];
    }
    $rl = &$_SESSION['rate_limit'][$key];

    // Reset window jika sudah expired
    if (time() - $rl['first_attempt'] > $windowSeconds) {
        $rl = ['count' => 0, 'first_attempt' => time()];
    }

    $rl['count']++;

    if ($rl['count'] > $maxAttempts) {
        return false; // Rate limited
    }
    return true;
}

/**
 * Format angka ke Rupiah
 * @param float $angka
 * @return string "Rp 35.000"
 */
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Redirect ke URL tertentu
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set flash message ke session
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,     // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Tampilkan flash message (sekali pakai)
 * @return string HTML
 */
function getFlash() {
    if (!isset($_SESSION['flash'])) return '';

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $icons = [
        'success' => '✓',
        'error'   => '✕',
        'warning' => '⚠',
        'info'    => 'ℹ'
    ];
    $type = e($flash['type'] ?? 'info');
    $icon = $icons[$flash['type']] ?? 'ℹ';

    return '<div class="flash-message flash-' . $type . '" id="flashMessage">
                <span class="flash-icon">' . $icon . '</span>
                <span class="flash-text">' . e($flash['message']) . '</span>
                <button class="flash-close" onclick="this.parentElement.remove()">×</button>
            </div>';
}

/**
 * Format tanggal ke bahasa Indonesia
 * @param string $date "2026-05-22"
 * @return string "Jumat, 22 Mei 2026"
 */
function formatTanggal($date) {
    $hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $timestamp = strtotime($date);
    $namaHari = $hari[date('l', $timestamp)];
    $tgl = date('j', $timestamp);
    $namaBulan = $bulan[(int)date('n', $timestamp)];
    $tahun = date('Y', $timestamp);

    return "$namaHari, $tgl $namaBulan $tahun";
}

/**
 * Format jam "09:00:00" → "09:00"
 */
function formatJam($time) {
    return date('H:i', strtotime($time));
}

/**
 * Generate time slots berdasarkan jam operasional
 * @return array ['09:00', '09:30', '10:00', ...]
 */
function generateTimeSlots() {
    $slots = [];
    $start = strtotime(JAM_BUKA);
    $end = strtotime(JAM_TUTUP);
    $interval = SLOT_INTERVAL * 60;

    for ($time = $start; $time < $end; $time += $interval) {
        $slots[] = date('H:i', $time);
    }
    return $slots;
}

/**
 * Cek apakah hari tersebut libur
 */
function isHariLibur($date) {
    $dayOfWeek = date('w', strtotime($date));
    return in_array((int)$dayOfWeek, HARI_LIBUR);
}

/**
 * Dapatkan slot yang sudah terisi untuk barber tertentu pada tanggal tertentu
 */
function getBookedSlots($conn, $barber_id, $tanggal) {
    $stmt = $conn->prepare("SELECT jam FROM booking WHERE barber_id = ? AND tanggal = ? AND status != 'Batal'");
    $stmt->bind_param('is', $barber_id, $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();

    $booked = [];
    while ($row = $result->fetch_assoc()) {
        $booked[] = formatJam($row['jam']);
    }
    $stmt->close();
    return $booked;
}

/**
 * Ambil semua layanan aktif
 */
function getLayananAktif($conn) {
    $result = $conn->query("SELECT * FROM layanan WHERE is_active = 1 ORDER BY harga ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Ambil semua barber aktif
 */
function getBarberAktif($conn) {
    $result = $conn->query("SELECT * FROM barber WHERE is_active = 1 ORDER BY nama ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Ambil statistik dashboard
 */
function getDashboardStats($conn) {
    $today = date('Y-m-d');

    // Total antrean hari ini
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM booking WHERE tanggal = ? AND status != 'Batal'");
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $totalAntrean = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Estimasi pendapatan hari ini
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_harga), 0) as total FROM booking WHERE tanggal = ? AND status = 'Selesai'");
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $pendapatan = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Layanan terpopuler
    $result = $conn->query("SELECT l.nama, COUNT(b.id) as jumlah FROM booking b JOIN layanan l ON b.layanan_id = l.id WHERE b.status != 'Batal' GROUP BY b.layanan_id ORDER BY jumlah DESC LIMIT 1");
    $topService = $result->fetch_assoc();

    // Booking pending
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM booking WHERE tanggal = ? AND status = 'Pending'");
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $pending = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    return [
        'total_antrean' => $totalAntrean,
        'pendapatan' => $pendapatan,
        'top_service' => $topService ? $topService['nama'] : '-',
        'pending' => $pending
    ];
}

/**
 * Generate nomor antrean unik
 */
function generateNomorAntrean() {
    return 'IF-' . date('ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));
}

/**
 * Escape output untuk HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ============================================
// SISTEM POIN & MEMBERSHIP
// ============================================

/**
 * Hitung tier berdasarkan total poin yang pernah dikumpulkan
 */
function hitungTier($totalPoinEarned) {
    if ($totalPoinEarned >= TIER_PLATINUM_MIN) return 'Platinum';
    if ($totalPoinEarned >= TIER_GOLD_MIN) return 'Gold';
    if ($totalPoinEarned >= TIER_SILVER_MIN) return 'Silver';
    return 'Bronze';
}

/**
 * Ambil total poin earned sepanjang waktu (untuk hitung tier)
 */
function getTotalPoinEarned($conn, $memberId) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(jumlah), 0) as total FROM poin_history WHERE member_id = ? AND jenis = 'Dapat'");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)$result['total'];
}

/**
 * Update tier member berdasarkan total poin earned
 */
function updateMemberTier($conn, $memberId) {
    $totalEarned = getTotalPoinEarned($conn, $memberId);
    $tier = hitungTier($totalEarned);
    $stmt = $conn->prepare("UPDATE member SET tier = ? WHERE id = ?");
    $stmt->bind_param('si', $tier, $memberId);
    $stmt->execute();
    $stmt->close();
    return $tier;
}

/**
 * Tambah poin ke member
 */
function addPoin($conn, $memberId, $bookingId, $jumlah, $keterangan = '') {
    // Update saldo poin
    $stmt = $conn->prepare("UPDATE member SET total_poin = total_poin + ? WHERE id = ?");
    $stmt->bind_param('ii', $jumlah, $memberId);
    $stmt->execute();
    $stmt->close();

    // Ambil saldo akhir
    $stmt = $conn->prepare("SELECT total_poin FROM member WHERE id = ?");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $saldo = $stmt->get_result()->fetch_assoc()['total_poin'];
    $stmt->close();

    // Catat history
    $stmt = $conn->prepare("INSERT INTO poin_history (member_id, booking_id, jenis, jumlah, saldo_akhir, keterangan) VALUES (?, ?, 'Dapat', ?, ?, ?)");
    $stmt->bind_param('iiiis', $memberId, $bookingId, $jumlah, $saldo, $keterangan);
    $stmt->execute();
    $stmt->close();

    // Update tier
    updateMemberTier($conn, $memberId);

    return $saldo;
}

/**
 * Redeem poin untuk reward
 * @return array ['success' => bool, 'message' => string, 'kode_voucher' => string|null]
 */
function redeemPoin($conn, $memberId, $rewardId) {
    // Ambil info reward
    $stmt = $conn->prepare("SELECT * FROM rewards WHERE id = ? AND is_active = 1");
    $stmt->bind_param('i', $rewardId);
    $stmt->execute();
    $reward = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$reward) {
        return ['success' => false, 'message' => 'Reward tidak ditemukan.'];
    }

    // Gunakan transaction untuk mencegah race condition
    $conn->begin_transaction();

    try {
        // Lock row member saat SELECT untuk mencegah concurrent redeem
        $stmt = $conn->prepare("SELECT total_poin FROM member WHERE id = ? FOR UPDATE");
        $stmt->bind_param('i', $memberId);
        $stmt->execute();
        $member = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($member['total_poin'] < $reward['poin_diperlukan']) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Poin tidak mencukupi. Butuh ' . $reward['poin_diperlukan'] . ' poin.'];
        }

        // Kurangi poin
        $poinUsed = $reward['poin_diperlukan'];
        $stmt = $conn->prepare("UPDATE member SET total_poin = total_poin - ? WHERE id = ?");
        $stmt->bind_param('ii', $poinUsed, $memberId);
        $stmt->execute();
        $stmt->close();

        // Ambil saldo akhir
        $stmt = $conn->prepare("SELECT total_poin FROM member WHERE id = ?");
        $stmt->bind_param('i', $memberId);
        $stmt->execute();
        $saldo = $stmt->get_result()->fetch_assoc()['total_poin'];
        $stmt->close();

        // Catat history redeem
        $stmt = $conn->prepare("INSERT INTO poin_history (member_id, booking_id, jenis, jumlah, saldo_akhir, keterangan) VALUES (?, NULL, 'Redeem', ?, ?, ?)");
        $keterangan = 'Tukar: ' . $reward['nama'];
        $stmt->bind_param('iiis', $memberId, $poinUsed, $saldo, $keterangan);
        $stmt->execute();
        $stmt->close();

        // Generate voucher
        $kodeVoucher = generateKodeVoucher();
        $expiredAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $stmt = $conn->prepare("INSERT INTO reward_claims (member_id, reward_id, poin_digunakan, kode_voucher, expired_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiis', $memberId, $rewardId, $poinUsed, $kodeVoucher, $expiredAt);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        return [
            'success' => true,
            'message' => 'Berhasil menukar ' . $reward['nama'] . '!',
            'kode_voucher' => $kodeVoucher
        ];
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Redeem poin error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi.'];
    }
}

/**
 * Ambil total poin member saat ini
 */
function getMemberPoin($conn, $memberId) {
    $stmt = $conn->prepare("SELECT total_poin, tier FROM member WHERE id = ?");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

/**
 * Ambil riwayat poin member
 */
function getPoinHistory($conn, $memberId, $limit = 20) {
    $stmt = $conn->prepare("SELECT ph.*, b.tanggal as booking_tanggal, l.nama as nama_layanan
                            FROM poin_history ph
                            LEFT JOIN booking b ON ph.booking_id = b.id
                            LEFT JOIN layanan l ON b.layanan_id = l.id
                            WHERE ph.member_id = ?
                            ORDER BY ph.created_at DESC
                            LIMIT ?");
    $stmt->bind_param('ii', $memberId, $limit);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

/**
 * Ambil daftar reward aktif
 */
function getAvailableRewards($conn) {
    $result = $conn->query("SELECT r.*, l.nama as nama_layanan FROM rewards r LEFT JOIN layanan l ON r.layanan_id = l.id WHERE r.is_active = 1 ORDER BY r.poin_diperlukan ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Ambil semua reward (untuk admin)
 */
function getAllRewards($conn) {
    $result = $conn->query("SELECT r.*, l.nama as nama_layanan FROM rewards r LEFT JOIN layanan l ON r.layanan_id = l.id ORDER BY r.poin_diperlukan ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Ambil reward claims member
 */
function getMemberClaims($conn, $memberId) {
    $stmt = $conn->prepare("SELECT rc.*, r.nama as reward_nama, r.jenis as reward_jenis, r.nilai_diskon, r.icon
                            FROM reward_claims rc
                            JOIN rewards r ON rc.reward_id = r.id
                            WHERE rc.member_id = ?
                            ORDER BY rc.created_at DESC");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

/**
 * Generate kode voucher unik
 */
function generateKodeVoucher() {
    return 'IFB-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

/**
 * Hitung poin dari booking
 */
function hitungPoinBooking($totalHarga) {
    return (int)floor($totalHarga / POIN_PER_RUPIAH);
}

/**
 * Info tier berikutnya
 */
function getNextTierInfo($currentTier, $totalPoinEarned) {
    switch ($currentTier) {
        case 'Bronze':
            return ['next' => 'Silver', 'needed' => TIER_SILVER_MIN - $totalPoinEarned, 'target' => TIER_SILVER_MIN];
        case 'Silver':
            return ['next' => 'Gold', 'needed' => TIER_GOLD_MIN - $totalPoinEarned, 'target' => TIER_GOLD_MIN];
        case 'Gold':
            return ['next' => 'Platinum', 'needed' => TIER_PLATINUM_MIN - $totalPoinEarned, 'target' => TIER_PLATINUM_MIN];
        default:
            return ['next' => null, 'needed' => 0, 'target' => 0];
    }
}

/**
 * Warna tier
 */
function getTierColor($tier) {
    switch ($tier) {
        case 'Bronze': return '#cd7f32';
        case 'Silver': return '#c0c0c0';
        case 'Gold': return '#ffd700';
        case 'Platinum': return '#e5e4e2';
        default: return '#cd7f32';
    }
}
