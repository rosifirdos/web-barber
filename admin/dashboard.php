<?php
/**
 * IF Barber — Admin Dashboard
 * Halaman utama admin yang menampilkan ringkasan statistik dan grafik
 */

$pageTitle = 'Dashboard Admin — IF Barber';
$headerTitle = 'Dashboard';
$activePage = 'dashboard';

include __DIR__ . '/header.php';

// Fetch stats using existing helper function
$stats = getDashboardStats($conn);

// Fetch weekly chart data (last 7 days)
$weeklyData = [];
$labels = [];
$counts = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = formatTanggal($date);
    // Get short name (e.g. "Jumat, 22 Mei" -> "22 Mei")
    $parts = explode(',', $dayName);
    $shortDay = isset($parts[1]) ? trim($parts[1]) : $date;
    // Extract day/month only (e.g. "22 Mei 2026" -> "22 Mei")
    $shortDay = preg_replace('/\s\d{4}$/', '', $shortDay);
    
    $labels[] = $shortDay;
    $weeklyData[$date] = 0; // initialize
}

// Query weekly booking counts
$query = "SELECT tanggal, COUNT(id) as jumlah 
          FROM booking 
          WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
            AND status != 'Batal' 
          GROUP BY tanggal";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (isset($weeklyData[$row['tanggal']])) {
            $weeklyData[$row['tanggal']] = (int)$row['jumlah'];
        }
    }
}
$counts = array_values($weeklyData);

// Fetch recent bookings (last 5)
$recentQuery = "SELECT b.*, l.nama as nama_layanan, bar.nama as nama_barber 
                FROM booking b 
                JOIN layanan l ON b.layanan_id = l.id 
                JOIN barber bar ON b.barber_id = bar.id 
                ORDER BY b.created_at DESC 
                LIMIT 5";
$recentResult = $conn->query($recentQuery);
$recentBookings = $recentResult ? $recentResult->fetch_all(MYSQLI_ASSOC) : [];
?>

<!-- ============================================
     STATS CARDS GRID
     ============================================ -->
<div class="stats-grid">
    <!-- Stat 1: Total Bookings Today -->
    <div class="stat-card">
        <div class="stat-card__info">
            <span class="stat-card__label">Antrean Hari Ini</span>
            <span class="stat-card__value"><?= $stats['total_antrean'] ?></span>
        </div>
        <div class="stat-card__icon">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
    </div>

    <!-- Stat 2: Pending Bookings -->
    <div class="stat-card">
        <div class="stat-card__info">
            <span class="stat-card__label">Pending</span>
            <span class="stat-card__value"><?= $stats['pending'] ?></span>
        </div>
        <div class="stat-card__icon">
            <i data-lucide="clock-arrow-up" style="width: 24px; height: 24px; color: #f59e0b;"></i>
        </div>
    </div>

    <!-- Stat 3: Today's Revenue -->
    <div class="stat-card">
        <div class="stat-card__info">
            <span class="stat-card__label">Pendapatan Hari Ini</span>
            <span class="stat-card__value" style="font-size: var(--text-lg); margin-top: 5px;"><?= formatRupiah($stats['pendapatan']) ?></span>
        </div>
        <div class="stat-card__icon">
            <i data-lucide="banknote" style="width: 24px; height: 24px; color: var(--color-success);"></i>
        </div>
    </div>

    <!-- Stat 4: Top Service -->
    <div class="stat-card">
        <div class="stat-card__info">
            <span class="stat-card__label">Layanan Terpopuler</span>
            <span class="stat-card__value" style="font-size: var(--text-md); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 170px; margin-top: 5px;" title="<?= e($stats['top_service']) ?>">
                <?= e($stats['top_service']) ?>
            </span>
        </div>
        <div class="stat-card__icon">
            <i data-lucide="award" style="width: 24px; height: 24px; color: #a855f7;"></i>
        </div>
    </div>
</div>

<div class="grid grid--2col" style="gap: var(--space-xl); margin-bottom: var(--space-xl);">
    <!-- ============================================
         CHART CONTAINER
         ============================================ -->
    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card__header">
            <h3 class="admin-card__title">
                <i data-lucide="trending-up"></i>
                Tren Antrean Mingguan
            </h3>
        </div>
        <div class="admin-card__body">
            <div class="chart-container">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ============================================
         QUICK ACTIONS PANEL
         ============================================ -->
    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card__header">
            <h3 class="admin-card__title">
                <i data-lucide="zap"></i>
                Aksi Cepat
            </h3>
        </div>
        <div class="admin-card__body" style="display: flex; flex-direction: column; gap: var(--space-md);">
            <p style="font-size: var(--text-sm); color: var(--color-text-muted); margin-bottom: 10px;">
                Gunakan tautan berikut untuk mengelola operasional barbershop dengan cepat:
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                <a href="<?= BASE_URL ?>/admin/antrian.php" class="btn btn--primary" style="justify-content: center; height: auto; padding: var(--space-md);">
                    <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
                    Daftar Antrean
                </a>
                <a href="<?= BASE_URL ?>/admin/layanan.php" class="btn btn--secondary" style="justify-content: center; height: auto; padding: var(--space-md); border-color: var(--glass-border);">
                    <i data-lucide="scissors" style="width: 18px; height: 18px;"></i>
                    Kelola Layanan
                </a>
            </div>
            
            <div class="glass" style="padding: var(--space-md); border-radius: var(--radius-md); margin-top: 15px;">
                <h4 style="font-size: var(--text-sm); font-weight: 600; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="info" style="width: 16px; height: 16px; color: var(--color-accent);"></i>
                    Info Operasional
                </h4>
                <ul style="font-size: var(--text-xs); color: var(--color-text-muted); padding-left: 15px; display: flex; flex-direction: column; gap: 4px;">
                    <li>Jam Buka: <strong><?= JAM_BUKA ?> WIB</strong> sampai <strong><?= JAM_TUTUP ?> WIB</strong></li>
                    <li>Interval Slot: <strong><?= SLOT_INTERVAL ?> menit</strong> per pelanggan</li>
                    <li>Hari Libur: <strong>Minggu</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
     RECENT BOOKINGS TABLE
     ============================================ -->
<div class="admin-card">
    <div class="admin-card__header">
        <h3 class="admin-card__title">
            <i data-lucide="clock"></i>
            Antrean Terbaru
        </h3>
        <a href="<?= BASE_URL ?>/admin/antrian.php" class="btn btn--ghost" style="padding: var(--space-xs) var(--space-md); font-size: var(--text-xs);">
            Lihat Semua
            <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i>
        </a>
    </div>
    <div class="admin-card__body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Barber</th>
                        <th>Waktu Cukur</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBookings)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 30px;">
                                Belum ada antrean masuk.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $booking): 
                            $statusClass = '';
                            if ($booking['status'] === 'Pending') $statusClass = 'badge--pending';
                            elseif ($booking['status'] === 'Proses') $statusClass = 'badge--process';
                            elseif ($booking['status'] === 'Selesai') $statusClass = 'badge--success';
                            elseif ($booking['status'] === 'Batal') $statusClass = 'badge--danger';
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?= e($booking['nama_pelanggan']) ?></div>
                                    <div style="font-size: var(--text-xs); color: var(--color-text-muted);"><?= e($booking['no_hp']) ?></div>
                                </td>
                                <td><?= e($booking['nama_layanan']) ?></td>
                                <td><?= e($booking['nama_barber']) ?></td>
                                <td>
                                    <div style="font-weight: 500;"><?= formatTanggal($booking['tanggal']) ?></div>
                                    <div style="font-size: var(--text-xs); color: var(--color-text-muted);"><?= formatJam($booking['jam']) ?> WIB</div>
                                </td>
                                <td style="font-weight: 600;"><?= formatRupiah($booking['total_harga']) ?></td>
                                <td>
                                    <span class="card__badge <?= $statusClass ?>"><?= $booking['status'] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================
     WEEKLY CHART JS INJECT
     ============================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('weeklyChart').getContext('2d');
    
    // Get colors from CSS Variables or set premium gradient
    var gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(200, 169, 110, 0.4)');
    gradient.addColorStop(1, 'rgba(200, 169, 110, 0.0)');

    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Jumlah Antrean',
                data: <?= json_encode($counts) ?>,
                borderColor: '#C8A96E',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#C8A96E',
                pointBorderColor: '#0a0a0f',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#12121d',
                    titleColor: '#fff',
                    bodyColor: '#aaa',
                    borderColor: 'rgba(255, 255, 255, 0.08)',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' antrean';
                        }
                    }
                }
            },
            scales: {
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.03)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#888',
                        font: {
                            family: 'Inter',
                            size: 11
                        },
                        stepSize: 1,
                        precision: 0
                    },
                    min: 0
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#888',
                        font: {
                            family: 'Inter',
                            size: 11
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
