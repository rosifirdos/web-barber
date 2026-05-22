<?php
/**
 * IF Barber — Member Dashboard
 */
$pageTitle = 'Dashboard Member — IF Barber';
$activePage = 'member';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Verifikasi login
if (!isMemberLoggedIn()) {
    redirect(BASE_URL . '/member/login.php');
}

$memberId = $_SESSION['member_id'];

// Ambil profil member
$stmt = $conn->prepare("SELECT * FROM member WHERE id = ?");
$stmt->bind_param('i', $memberId);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil riwayat booking member
$query = "SELECT b.*, l.nama as nama_layanan, bar.nama as nama_barber 
          FROM booking b 
          JOIN layanan l ON b.layanan_id = l.id 
          JOIN barber bar ON b.barber_id = bar.id 
          WHERE b.member_id = ? 
          ORDER BY b.tanggal DESC, b.jam DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $memberId);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once BASE_PATH . '/includes/header.php';
?>

<div style="min-height: 80vh; padding: var(--space-xl) var(--space-md); background: var(--bg-dark);">
    <div class="container" style="max-width: 1000px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg); flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="font-family: var(--font-heading); color: var(--color-accent); margin-bottom: var(--space-xs);">Dashboard Akun</h1>
                <p style="color: var(--color-text-muted);">Selamat datang kembali, <?= e($member['nama']) ?>.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= BASE_URL ?>/booking.php" class="btn btn--primary">
                    <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 5px;"></i> Booking Baru
                </a>
                <a href="<?= BASE_URL ?>/member/logout.php" class="btn btn--secondary">
                    <i data-lucide="log-out" style="width: 18px; height: 18px; margin-right: 5px;"></i> Logout
                </a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
            <!-- Profil Card -->
            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Profil Anda</h3>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div>
                        <div style="font-size: var(--text-sm); color: var(--color-text-muted);">Nama Lengkap</div>
                        <div style="font-weight: 500;"><?= e($member['nama']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: var(--text-sm); color: var(--color-text-muted);">Email</div>
                        <div style="font-weight: 500;"><?= e($member['email']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: var(--text-sm); color: var(--color-text-muted);">Nomor HP</div>
                        <div style="font-weight: 500;"><?= e($member['no_hp']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: var(--text-sm); color: var(--color-text-muted);">Member Sejak</div>
                        <div style="font-weight: 500;"><?= date('d F Y', strtotime($member['created_at'])) ?></div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Booking -->
            <div class="card" style="padding: 0;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                    <h3 style="font-size: 1.2rem; margin: 0;">Riwayat Booking</h3>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div style="padding: 40px 20px; text-align: center; color: var(--color-text-muted);">
                        <i data-lucide="calendar" style="width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>Anda belum memiliki riwayat booking.</p>
                        <a href="<?= BASE_URL ?>/booking.php" class="btn btn--primary btn--sm" style="margin-top: 15px;">Pesan Sekarang</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: var(--bg-card-alt); border-bottom: 1px solid var(--border-color);">
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Tanggal & Jam</th>
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Layanan</th>
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Barber</th>
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): 
                                    $statusColor = 'var(--color-text-muted)';
                                    if ($b['status'] === 'Pending') $statusColor = '#f39c12';
                                    elseif ($b['status'] === 'Proses') $statusColor = '#3498db';
                                    elseif ($b['status'] === 'Selesai') $statusColor = '#2ecc71';
                                    elseif ($b['status'] === 'Batal') $statusColor = '#e74c3c';
                                ?>
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td style="padding: 15px 20px;">
                                            <div style="font-weight: 500;"><?= formatTanggal($b['tanggal']) ?></div>
                                            <div style="font-size: var(--text-xs); color: var(--color-text-muted);"><?= formatJam($b['jam']) ?> WIB</div>
                                        </td>
                                        <td style="padding: 15px 20px;"><?= e($b['nama_layanan']) ?></td>
                                        <td style="padding: 15px 20px;"><?= e($b['nama_barber']) ?></td>
                                        <td style="padding: 15px 20px;">
                                            <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: <?= $statusColor ?>20; color: <?= $statusColor ?>;">
                                                <?= $b['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .container > div:nth-child(2) {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
