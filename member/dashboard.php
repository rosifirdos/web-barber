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

// Ambil data poin & tier
$poinInfo = getMemberPoin($conn, $memberId);
$totalPoinEarned = getTotalPoinEarned($conn, $memberId);
$tierColor = getTierColor($poinInfo['tier']);
$nextTier = getNextTierInfo($poinInfo['tier'], $totalPoinEarned);

// Ambil rewards, claims, history
$rewards = getAvailableRewards($conn);
$claims = getMemberClaims($conn, $memberId);
$poinHistory = getPoinHistory($conn, $memberId);

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

// Hitung statistik
$totalBooking = count($bookings);
$totalSelesai = count(array_filter($bookings, fn($b) => $b['status'] === 'Selesai'));
$activeClaims = count(array_filter($claims, fn($c) => $c['status'] === 'Aktif'));

// Tab aktif
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

require_once BASE_PATH . '/includes/header.php';
?>

<div style="min-height: 80vh; padding: calc(var(--navbar-height, 80px) + var(--space-xl)) var(--space-md) var(--space-xl); background: var(--color-bg-primary);">
    <div class="container" style="max-width: 1100px; margin: 0 auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg); flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="font-family: var(--font-heading); color: var(--color-accent); margin-bottom: var(--space-xs);">Dashboard Member</h1>
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

        <!-- ============================================
             POIN & TIER CARD
             ============================================ -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <!-- Poin Card -->
            <div class="card" style="background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05)); border: 1px solid rgba(212,175,55,0.3);">
                <div class="card__body" style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(212,175,55,0.2); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="coins" style="width: 24px; height: 24px; color: var(--color-accent);"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--text-sm); color: var(--color-text-muted);">Total Poin</div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: var(--color-accent);"><?= number_format($poinInfo['total_poin']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Tier Card -->
            <div class="card" style="background: linear-gradient(135deg, <?= $tierColor ?>22, <?= $tierColor ?>08); border: 1px solid <?= $tierColor ?>44;">
                <div class="card__body" style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: <?= $tierColor ?>33; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="award" style="width: 24px; height: 24px; color: <?= $tierColor ?>;"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--text-sm); color: var(--color-text-muted);">Tier Membership</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: <?= $tierColor ?>;"><?= $poinInfo['tier'] ?></div>
                        <?php if ($nextTier['next']): ?>
                            <div style="font-size: var(--text-xs); color: var(--color-text-muted); margin-top: 2px;">
                                <?= max(0, $nextTier['needed']) ?> poin lagi ke <?= $nextTier['next'] ?>
                            </div>
                        <?php else: ?>
                            <div style="font-size: var(--text-xs); color: var(--color-text-muted); margin-top: 2px;">Tier tertinggi! 🏆</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Voucher Aktif Card -->
            <div class="card" style="background: linear-gradient(135deg, rgba(46,204,113,0.15), rgba(46,204,113,0.05)); border: 1px solid rgba(46,204,113,0.3);">
                <div class="card__body" style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(46,204,113,0.2); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="ticket" style="width: 24px; height: 24px; color: #2ecc71;"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--text-sm); color: var(--color-text-muted);">Voucher Aktif</div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: #2ecc71;"><?= $activeClaims ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tier Progress Bar -->
        <?php if ($nextTier['next']): 
            $progress = ($nextTier['target'] > 0) ? min(100, ($totalPoinEarned / $nextTier['target']) * 100) : 100;
        ?>
        <div class="card" style="margin-bottom: 30px;">
            <div class="card__body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-size: var(--text-sm); color: var(--color-text-muted);">Progress ke <strong style="color: <?= getTierColor($nextTier['next']) ?>;"><?= $nextTier['next'] ?></strong></span>
                    <span style="font-size: var(--text-sm); color: var(--color-text-muted);"><?= number_format($totalPoinEarned) ?> / <?= number_format($nextTier['target']) ?> poin</span>
                </div>
                <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
                    <div style="width: <?= $progress ?>%; height: 100%; background: linear-gradient(90deg, <?= $tierColor ?>, <?= getTierColor($nextTier['next']) ?>); border-radius: 4px; transition: width 0.5s ease;"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============================================
             TAB NAVIGATION
             ============================================ -->
        <div style="display: flex; gap: 5px; margin-bottom: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 0; overflow-x: auto;">
            <a href="?tab=overview" class="member-tab <?= $activeTab === 'overview' ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i> Overview
            </a>
            <a href="?tab=rewards" class="member-tab <?= $activeTab === 'rewards' ? 'active' : '' ?>">
                <i data-lucide="gift" style="width: 16px; height: 16px;"></i> Tukar Poin
            </a>
            <a href="?tab=vouchers" class="member-tab <?= $activeTab === 'vouchers' ? 'active' : '' ?>">
                <i data-lucide="ticket" style="width: 16px; height: 16px;"></i> Voucher Saya
            </a>
            <a href="?tab=history" class="member-tab <?= $activeTab === 'history' ? 'active' : '' ?>">
                <i data-lucide="history" style="width: 16px; height: 16px;"></i> Riwayat Poin
            </a>
        </div>

        <!-- ============================================
             TAB: OVERVIEW (Profil + Booking History)
             ============================================ -->
        <?php if ($activeTab === 'overview'): ?>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
            <!-- Profil Card -->
            <div class="card">
                <div class="card__body">
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
                        <div>
                            <div style="font-size: var(--text-sm); color: var(--color-text-muted);">Total Kunjungan</div>
                            <div style="font-weight: 500;"><?= $totalSelesai ?> kali</div>
                        </div>
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
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): 
                                    $statusColor = 'var(--color-text-muted)';
                                    if ($b['status'] === 'Pending') $statusColor = '#f39c12';
                                    elseif ($b['status'] === 'Proses') $statusColor = '#3498db';
                                    elseif ($b['status'] === 'Selesai') $statusColor = '#2ecc71';
                                    elseif ($b['status'] === 'Batal') $statusColor = '#e74c3c';
                                    $poinEarned = ($b['status'] === 'Selesai') ? hitungPoinBooking($b['total_harga']) : 0;
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
                                        <td style="padding: 15px 20px;">
                                            <?php if ($poinEarned > 0): ?>
                                                <span style="color: #2ecc71; font-weight: 600;">+<?= $poinEarned ?></span>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================
             TAB: REWARDS (Tukar Poin)
             ============================================ -->
        <?php elseif ($activeTab === 'rewards'): ?>
        <div>
            <div style="margin-bottom: 20px;">
                <h2 style="font-size: 1.3rem; margin-bottom: 5px;">Tukar Poin Anda</h2>
                <p style="color: var(--color-text-muted);">Saldo poin Anda: <strong style="color: var(--color-accent);"><?= number_format($poinInfo['total_poin']) ?> poin</strong></p>
            </div>

            <?php if (empty($rewards)): ?>
                <div class="card" style="text-align: center; padding: 60px 20px;">
                    <i data-lucide="gift" style="width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p style="color: var(--color-text-muted);">Belum ada reward yang tersedia saat ini.</p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <?php foreach ($rewards as $r): 
                        $canAfford = $poinInfo['total_poin'] >= $r['poin_diperlukan'];
                        $iconName = $r['icon'] ?: 'gift';
                    ?>
                    <div class="card reward-card" style="position: relative; overflow: hidden; <?= !$canAfford ? 'opacity: 0.6;' : '' ?>">
                        <div class="card__body">
                            <!-- Reward Icon & Info -->
                            <div style="display: flex; align-items: flex-start; gap: 15px; margin-bottom: 15px;">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(212,175,55,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i data-lucide="<?= e($iconName) ?>" style="width: 24px; height: 24px; color: var(--color-accent);"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 1.05rem; margin-bottom: 4px;"><?= e($r['nama']) ?></h4>
                                    <p style="font-size: var(--text-sm); color: var(--color-text-muted); line-height: 1.4;"><?= e($r['deskripsi']) ?></p>
                                </div>
                            </div>

                            <!-- Poin & Button -->
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid var(--border-color);">
                                <div>
                                    <span style="font-size: 1.2rem; font-weight: 800; color: var(--color-accent);"><?= number_format($r['poin_diperlukan']) ?></span>
                                    <span style="font-size: var(--text-sm); color: var(--color-text-muted);"> poin</span>
                                </div>
                                <?php if ($canAfford): ?>
                                    <button class="btn btn--primary btn--sm" onclick="redeemReward(<?= $r['id'] ?>, '<?= e($r['nama']) ?>', <?= $r['poin_diperlukan'] ?>)">
                                        <i data-lucide="arrow-right-left" style="width: 14px; height: 14px; margin-right: 4px;"></i> Tukar
                                    </button>
                                <?php else: ?>
                                    <span style="font-size: var(--text-sm); color: var(--color-text-muted); font-style: italic;">Poin belum cukup</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================
             TAB: VOUCHER SAYA
             ============================================ -->
        <?php elseif ($activeTab === 'vouchers'): ?>
        <div>
            <h2 style="font-size: 1.3rem; margin-bottom: 20px;">Voucher Saya</h2>

            <?php if (empty($claims)): ?>
                <div class="card" style="text-align: center; padding: 60px 20px;">
                    <i data-lucide="ticket" style="width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p style="color: var(--color-text-muted);">Anda belum memiliki voucher. <a href="?tab=rewards" style="color: var(--color-accent);">Tukar poin sekarang</a></p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;">
                    <?php foreach ($claims as $c):
                        $isActive = $c['status'] === 'Aktif';
                        $isExpired = $c['status'] === 'Aktif' && strtotime($c['expired_at']) < time();
                        if ($isExpired) {
                            // Auto-expire
                            $upd = $conn->prepare("UPDATE reward_claims SET status = 'Kadaluarsa' WHERE id = ?");
                            $upd->bind_param('i', $c['id']);
                            $upd->execute();
                            $upd->close();
                            $c['status'] = 'Kadaluarsa';
                            $isActive = false;
                        }
                        $statusColor = '#2ecc71';
                        if ($c['status'] === 'Terpakai') $statusColor = '#3498db';
                        if ($c['status'] === 'Kadaluarsa') $statusColor = '#e74c3c';
                    ?>
                    <div class="card" style="<?= !$isActive ? 'opacity: 0.65;' : '' ?>">
                        <div class="card__body">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i data-lucide="<?= e($c['icon'] ?: 'gift') ?>" style="width: 20px; height: 20px; color: var(--color-accent);"></i>
                                    <h4 style="font-size: 1rem;"><?= e($c['reward_nama']) ?></h4>
                                </div>
                                <span style="display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: <?= $statusColor ?>20; color: <?= $statusColor ?>;">
                                    <?= $c['status'] ?>
                                </span>
                            </div>

                            <!-- Kode Voucher -->
                            <div style="background: rgba(212,175,55,0.1); border: 1px dashed rgba(212,175,55,0.4); border-radius: 8px; padding: 12px; text-align: center; margin-bottom: 12px;">
                                <div style="font-size: var(--text-xs); color: var(--color-text-muted); margin-bottom: 4px;">Kode Voucher</div>
                                <div style="font-size: 1.2rem; font-weight: 800; letter-spacing: 2px; color: var(--color-accent);"><?= e($c['kode_voucher']) ?></div>
                            </div>

                            <div style="display: flex; justify-content: space-between; font-size: var(--text-xs); color: var(--color-text-muted);">
                                <span>Ditukar: <?= date('d M Y', strtotime($c['created_at'])) ?></span>
                                <span>Berlaku s/d: <?= date('d M Y', strtotime($c['expired_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================
             TAB: RIWAYAT POIN
             ============================================ -->
        <?php elseif ($activeTab === 'history'): ?>
        <div>
            <h2 style="font-size: 1.3rem; margin-bottom: 20px;">Riwayat Poin</h2>

            <?php if (empty($poinHistory)): ?>
                <div class="card" style="text-align: center; padding: 60px 20px;">
                    <i data-lucide="history" style="width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p style="color: var(--color-text-muted);">Belum ada riwayat poin.</p>
                </div>
            <?php else: ?>
                <div class="card" style="padding: 0;">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: var(--bg-card-alt); border-bottom: 1px solid var(--border-color);">
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Tanggal</th>
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Keterangan</th>
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Poin</th>
                                    <th style="padding: 15px 20px; font-weight: 600; color: var(--color-text-muted);">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($poinHistory as $ph): ?>
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td style="padding: 15px 20px; font-size: var(--text-sm);">
                                            <?= date('d M Y, H:i', strtotime($ph['created_at'])) ?>
                                        </td>
                                        <td style="padding: 15px 20px;">
                                            <div style="font-weight: 500;"><?= e($ph['keterangan']) ?></div>
                                            <?php if ($ph['nama_layanan']): ?>
                                                <div style="font-size: var(--text-xs); color: var(--color-text-muted);"><?= e($ph['nama_layanan']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px 20px;">
                                            <?php if ($ph['jenis'] === 'Dapat'): ?>
                                                <span style="color: #2ecc71; font-weight: 700;">+<?= number_format($ph['jumlah']) ?></span>
                                            <?php else: ?>
                                                <span style="color: #e74c3c; font-weight: 700;">-<?= number_format($ph['jumlah']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px 20px; font-weight: 500;"><?= number_format($ph['saldo_akhir']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ============================================
     MODAL: KONFIRMASI REDEEM
     ============================================ -->
<div id="redeemModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div style="background: var(--color-bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 30px; max-width: 420px; width: 90%; text-align: center;">
        <i data-lucide="gift" style="width: 48px; height: 48px; color: var(--color-accent); margin-bottom: 15px;"></i>
        <h3 style="margin-bottom: 10px;" id="redeemTitle">Konfirmasi Penukaran</h3>
        <p style="color: var(--color-text-muted); margin-bottom: 20px;" id="redeemDesc">Apakah Anda yakin ingin menukar <strong id="redeemPoin">0</strong> poin untuk <strong id="redeemName">reward</strong>?</p>
        
        <div id="redeemResult" style="display: none; margin-bottom: 20px;"></div>
        
        <div id="redeemButtons" style="display: flex; gap: 10px; justify-content: center;">
            <button class="btn btn--secondary" onclick="closeRedeemModal()">Batal</button>
            <button class="btn btn--primary" id="confirmRedeemBtn" onclick="confirmRedeem()">
                <i data-lucide="check" style="width: 16px; height: 16px; margin-right: 4px;"></i> Ya, Tukar
            </button>
        </div>
    </div>
</div>

<style>
    .member-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        color: var(--color-text-muted);
        text-decoration: none;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: var(--text-sm);
        white-space: nowrap;
    }
    .member-tab:hover {
        color: var(--color-text-primary);
    }
    .member-tab.active {
        color: var(--color-accent);
        border-bottom-color: var(--color-accent);
    }
    .reward-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .reward-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }
    @media (max-width: 768px) {
        .container > div:nth-child(2) {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 640px) {
        div[style*="grid-template-columns: 1fr 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        div[style*="grid-template-columns: 1fr 2fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
var currentRedeemId = null;

function redeemReward(id, name, poin) {
    currentRedeemId = id;
    document.getElementById('redeemName').textContent = name;
    document.getElementById('redeemPoin').textContent = poin.toLocaleString();
    document.getElementById('redeemResult').style.display = 'none';
    document.getElementById('redeemButtons').style.display = 'flex';
    document.getElementById('redeemDesc').style.display = 'block';
    document.getElementById('redeemModal').style.display = 'flex';
}

function closeRedeemModal() {
    document.getElementById('redeemModal').style.display = 'none';
    currentRedeemId = null;
}

function confirmRedeem() {
    if (!currentRedeemId) return;
    
    var btn = document.getElementById('confirmRedeemBtn');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;">Memproses...</span>';

    fetch(BASE_URL + '/api/redeem_reward.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ reward_id: currentRedeemId })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var resultDiv = document.getElementById('redeemResult');
        document.getElementById('redeemDesc').style.display = 'none';
        document.getElementById('redeemButtons').style.display = 'none';
        resultDiv.style.display = 'block';

        if (data.status === 'success') {
            resultDiv.innerHTML = '<div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);border-radius:12px;padding:20px;">' +
                '<div style="color:#2ecc71;font-size:1.1rem;font-weight:700;margin-bottom:8px;">✓ Berhasil!</div>' +
                '<p style="color:var(--color-text-muted);margin-bottom:15px;">' + data.message + '</p>' +
                '<div style="background:rgba(212,175,55,0.1);border:1px dashed rgba(212,175,55,0.4);border-radius:8px;padding:12px;">' +
                '<div style="font-size:var(--text-xs);color:var(--color-text-muted);margin-bottom:4px;">Kode Voucher Anda</div>' +
                '<div style="font-size:1.3rem;font-weight:800;letter-spacing:2px;color:var(--color-accent);">' + data.kode_voucher + '</div>' +
                '</div>' +
                '<p style="margin-top:12px;font-size:var(--text-sm);color:var(--color-text-muted);">Sisa poin: ' + data.sisa_poin.toLocaleString() + '</p>' +
                '</div>' +
                '<button class="btn btn--primary" style="margin-top:15px;" onclick="location.reload()">Tutup</button>';
        } else {
            resultDiv.innerHTML = '<div style="color:#e74c3c;font-weight:600;margin-bottom:10px;">✕ Gagal</div>' +
                '<p style="color:var(--color-text-muted);">' + data.message + '</p>' +
                '<button class="btn btn--secondary" style="margin-top:15px;" onclick="closeRedeemModal()">Tutup</button>';
        }
    })
    .catch(function(err) {
        console.error('Redeem error:', err);
        alert('Terjadi kesalahan koneksi.');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="check" style="width:16px;height:16px;margin-right:4px;"></i> Ya, Tukar';
    });
}
</script>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
