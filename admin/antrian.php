<?php
/**
 * IF Barber — Kelola Antrean
 * CRUD & Manajemen antrean booking pelanggan
 */

$pageTitle = 'Kelola Antrean — IF Barber';
$headerTitle = 'Kelola Antrean';
$activePage = 'antrian';

include __DIR__ . '/header.php';

// Handle Action (Ubah Status / Batal)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Check if booking exists
    $checkStmt = $conn->prepare("SELECT id, status FROM booking WHERE id = ?");
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $bookingExists = $checkStmt->get_result()->num_rows === 1;
    $checkStmt->close();

    if ($bookingExists) {
        if ($action === 'proses') {
            $stmt = $conn->prepare("UPDATE booking SET status = 'Proses' WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                setFlash('success', 'Antrean #' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' sedang diproses.');
            } else {
                setFlash('error', 'Gagal memproses antrean.');
            }
            $stmt->close();
        } elseif ($action === 'selesai') {
            $stmt = $conn->prepare("UPDATE booking SET status = 'Selesai' WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                // Auto-assign poin jika booking milik member
                $poinStmt = $conn->prepare("SELECT member_id, total_harga FROM booking WHERE id = ?");
                $poinStmt->bind_param('i', $id);
                $poinStmt->execute();
                $bookingData = $poinStmt->get_result()->fetch_assoc();
                $poinStmt->close();

                $poinMsg = '';
                if ($bookingData && $bookingData['member_id']) {
                    $poinEarned = hitungPoinBooking($bookingData['total_harga']);
                    if ($poinEarned > 0) {
                        addPoin($conn, $bookingData['member_id'], $id, $poinEarned, 'Poin dari booking #' . str_pad($id, 4, '0', STR_PAD_LEFT));
                        $poinMsg = ' (+' . $poinEarned . ' poin untuk member)';
                    }
                }

                setFlash('success', 'Antrean #' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' telah selesai.' . $poinMsg);
            } else {
                setFlash('error', 'Gagal menyelesaikan antrean.');
            }
            $stmt->close();
        } elseif ($action === 'batal') {
            $stmt = $conn->prepare("UPDATE booking SET status = 'Batal' WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                setFlash('success', 'Antrean #' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' berhasil dibatalkan.');
            } else {
                setFlash('error', 'Gagal membatalkan antrean.');
            }
            $stmt->close();
        }
    }
    // Redirect back to clean up URL
    $queryParams = $_GET;
    unset($queryParams['action'], $queryParams['id']);
    $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
    redirect(BASE_URL . '/admin/antrian.php' . $queryString);
}

// Filter Setup
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$whereConditions = [];
$params = [];
$types = '';

if ($search !== '') {
    $whereConditions[] = "b.nama_pelanggan LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if ($statusFilter !== '' && in_array($statusFilter, ['Pending', 'Proses', 'Selesai', 'Batal'])) {
    $whereConditions[] = "b.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($startDate !== '') {
    $whereConditions[] = "b.tanggal >= ?";
    $params[] = $startDate;
    $types .= 's';
}

if ($endDate !== '') {
    $whereConditions[] = "b.tanggal <= ?";
    $params[] = $endDate;
    $types .= 's';
}

$whereClause = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM booking b" . $whereClause;
if (!empty($params)) {
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->bind_param($types, ...$params);
    $stmtCount->execute();
    $totalResult = $stmtCount->get_result();
    $totalRows = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;
    $stmtCount->close();
} else {
    $totalResult = $conn->query($countQuery);
    $totalRows = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;
}
$totalPages = ceil($totalRows / $limit);

// Fetch data with Join for Service and Barber
$bookingQuery = "SELECT b.*, l.nama as nama_layanan, l.harga as harga_layanan, bar.nama as nama_barber 
                 FROM booking b 
                 JOIN layanan l ON b.layanan_id = l.id 
                 JOIN barber bar ON b.barber_id = bar.id 
                 $whereClause
                 ORDER BY b.tanggal DESC, b.jam DESC 
                 LIMIT ? OFFSET ?";

$stmt = $conn->prepare($bookingQuery);
$typesWithLimit = $types . 'ii';
$paramsWithLimit = array_merge($params, [$limit, $offset]);
// Extract values for bind_param dynamically
$bindParams = [];
$bindParams[] = &$typesWithLimit;
for ($i = 0; $i < count($paramsWithLimit); $i++) {
    $bindParams[] = &$paramsWithLimit[$i];
}
call_user_func_array([$stmt, 'bind_param'], $bindParams);

$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build Query String for pagination
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
?>

<!-- ============================================
     TABLE OF BOOKINGS
     ============================================ -->
<div class="admin-card">
    <div class="admin-card__header" style="flex-direction: column; align-items: stretch; gap: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="admin-card__title">
                <i data-lucide="list-ordered"></i>
                Daftar Antrean Pelanggan
            </h3>
        </div>
        
        <!-- Filter Form -->
        <form method="GET" action="" class="filter-bar" style="display: flex; flex-wrap: wrap; gap: 15px; background: var(--bg-card-alt); padding: 15px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label style="font-size: var(--text-sm); margin-bottom: 5px; display: block;">Cari Nama</label>
                <input type="text" name="search" class="form-input" placeholder="Nama Pelanggan..." value="<?= htmlspecialchars($search) ?>" style="width: 100%;">
            </div>
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label style="font-size: var(--text-sm); margin-bottom: 5px; display: block;">Status</label>
                <select name="status" class="form-input" style="width: 100%;">
                    <option value="">Semua Status</option>
                    <option value="Pending Payment" <?= $statusFilter === 'Pending Payment' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                    <option value="Confirmed" <?= $statusFilter === 'Confirmed' ? 'selected' : '' ?>>Terkonfirmasi (Sudah DP)</option>
                    <option value="Proses" <?= $statusFilter === 'Proses' ? 'selected' : '' ?>>Proses</option>
                    <option value="Selesai" <?= $statusFilter === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="Batal" <?= $statusFilter === 'Batal' ? 'selected' : '' ?>>Batal</option>
                    <option value="Expired" <?= $statusFilter === 'Expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 130px;">
                <label style="font-size: var(--text-sm); margin-bottom: 5px; display: block;">Mulai Tanggal</label>
                <input type="date" name="start_date" class="form-input" value="<?= htmlspecialchars($startDate) ?>" style="width: 100%;">
            </div>
            <div class="form-group" style="flex: 1; min-width: 130px;">
                <label style="font-size: var(--text-sm); margin-bottom: 5px; display: block;">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-input" value="<?= htmlspecialchars($endDate) ?>" style="width: 100%;">
            </div>
            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn--primary" style="height: 42px;">Filter</button>
                <?php if (!empty($_GET) && (!isset($_GET['page']) || count($_GET) > 1)): ?>
                    <a href="?" class="btn btn--secondary" style="height: 42px; margin-left: 10px;">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="admin-card__body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">No. Order</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Barber</th>
                        <th>Tanggal & Jam</th>
                        <th>Total Harga</th>
                        <th>Status Booking</th>
                        <th>Pembayaran</th>
                        <th style="width: 160px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--color-text-muted); padding: 40px;">
                                Belum ada antrean terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): 
                            $statusClass = '';
                            if ($b['status'] === 'Pending Payment') $statusClass = 'badge--pending';
                            elseif ($b['status'] === 'Confirmed') $statusClass = 'badge--success';
                            elseif ($b['status'] === 'Proses') $statusClass = 'badge--process';
                            elseif ($b['status'] === 'Selesai') $statusClass = 'badge--success';
                            elseif ($b['status'] === 'Batal' || $b['status'] === 'Expired') $statusClass = 'badge--danger';

                            // Format JSON for detail modal
                            $jsonObj = [
                                'id' => str_pad($b['id'], 4, '0', STR_PAD_LEFT),
                                'nama' => $b['nama_pelanggan'],
                                'no_hp' => $b['no_hp'],
                                'layanan' => $b['nama_layanan'],
                                'barber' => $b['nama_barber'],
                                'tanggal' => formatTanggal($b['tanggal']),
                                'jam' => formatJam($b['jam']),
                                'harga' => formatRupiah($b['total_harga']),
                                'dp' => formatRupiah($b['jumlah_dp']),
                                'status_pembayaran' => $b['status_pembayaran'],
                                'catatan' => $b['catatan'] ? $b['catatan'] : '-',
                                'status' => $b['status'],
                                'dibuat' => date('d/m/Y H:i', strtotime($b['created_at']))
                            ];
                            $jsonStr = htmlspecialchars(json_encode($jsonObj), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--color-accent);">
                                    #<?= str_pad($b['id'], 4, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?= e($b['nama_pelanggan']) ?></div>
                                    <div style="font-size: var(--text-xs); color: var(--color-text-muted);"><?= e($b['no_hp']) ?></div>
                                </td>
                                <td><?= e($b['nama_layanan']) ?></td>
                                <td><?= e($b['nama_barber']) ?></td>
                                <td>
                                    <div style="font-weight: 500;"><?= formatTanggal($b['tanggal']) ?></div>
                                    <div style="font-size: var(--text-xs); color: var(--color-text-muted);"><?= formatJam($b['jam']) ?> WIB</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?= formatRupiah($b['total_harga']) ?></div>
                                    <div style="font-size: var(--text-xs); color: var(--color-accent);">DP: <?= formatRupiah($b['jumlah_dp']) ?></div>
                                </td>
                                <td>
                                    <span class="card__badge <?= $statusClass ?>"><?= $b['status'] ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $payClass = $b['status_pembayaran'] === 'Belum Bayar' ? 'badge--danger' : 
                                                   ($b['status_pembayaran'] === 'Sudah DP' ? 'badge--pending' : 'badge--success');
                                    ?>
                                    <span class="card__badge <?= $payClass ?>"><?= $b['status_pembayaran'] ?></span>
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: center;">
                                        <!-- View Detail -->
                                        <button class="btn-action btn-action--view" onclick="viewDetail('<?= $jsonStr ?>')" title="Detail Booking">
                                            <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                                        </button>

                                        <!-- Quick Status Actions -->
                                        <?php if ($b['status'] === 'Pending Payment'): ?>
                                            <!-- Change to Batal -->
                                            <button class="btn-action btn-action--delete" onclick="confirmAction('Apakah Anda yakin ingin membatalkan booking #<?= str_pad($b['id'], 4, '0', STR_PAD_LEFT) ?>?', '?action=batal&id=<?= $b['id'] ?>&page=<?= $page ?><?= $queryString ?>')" title="Batalkan Booking">
                                                <i data-lucide="ban" style="width: 15px; height: 15px;"></i>
                                            </button>
                                        <?php elseif ($b['status'] === 'Confirmed'): ?>
                                            <!-- Change to Proses -->
                                            <a href="?action=proses&id=<?= $b['id'] ?>&page=<?= $page ?><?= $queryString ?>" class="btn-action btn-action--process" title="Mulai Proses Cukur">
                                                <i data-lucide="play" style="width: 15px; height: 15px;"></i>
                                            </a>
                                            <!-- Change to Batal -->
                                            <button class="btn-action btn-action--delete" onclick="confirmAction('Apakah Anda yakin ingin membatalkan booking #<?= str_pad($b['id'], 4, '0', STR_PAD_LEFT) ?>?', '?action=batal&id=<?= $b['id'] ?>&page=<?= $page ?><?= $queryString ?>')" title="Batalkan Booking">
                                                <i data-lucide="ban" style="width: 15px; height: 15px;"></i>
                                            </button>
                                        <?php elseif ($b['status'] === 'Proses'): ?>
                                            <!-- Change to Selesai -->
                                            <a href="?action=selesai&id=<?= $b['id'] ?>&page=<?= $page ?><?= $queryString ?>" class="btn-action btn-action--success" title="Selesaikan Layanan">
                                                <i data-lucide="check" style="width: 15px; height: 15px;"></i>
                                            </a>
                                            <!-- Change to Batal -->
                                            <button class="btn-action btn-action--delete" onclick="confirmAction('Apakah Anda yakin ingin membatalkan booking #<?= str_pad($b['id'], 4, '0', STR_PAD_LEFT) ?>?', '?action=batal&id=<?= $b['id'] ?>&page=<?= $page ?><?= $queryString ?>')" title="Batalkan Booking">
                                                <i data-lucide="ban" style="width: 15px; height: 15px;"></i>
                                            </button>
                                        <?php elseif ($b['status'] === 'Selesai'): ?>
                                            <!-- Cetak Struk -->
                                            <a href="<?= BASE_URL ?>/admin/cetak-struk.php?id=<?= $b['id'] ?>" target="_blank" class="btn-action btn-action--view" title="Cetak Struk PDF">
                                                <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
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
     PAGINATION CONTROL
     ============================================ -->
<?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top: var(--space-xl); justify-content: flex-end;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $queryString ?>" class="pagination__btn">
                <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i>
                Sebelumnya
            </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?><?= $queryString ?>" class="pagination__btn <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= $queryString ?>" class="pagination__btn">
                Berikutnya
                <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- ============================================
     MODAL: DETAIL BOOKING
     ============================================ -->
<div class="admin-modal" id="detailModal">
    <div class="admin-modal__overlay"></div>
    <div class="admin-modal__container">
        <div class="admin-modal__header">
            <h3 class="admin-modal__title">Detail Booking Order <span id="modalOrderId" style="color: var(--color-accent);"></span></h3>
            <button class="admin-modal__close">×</button>
        </div>
        <div class="admin-modal__body">
            <div class="detail-list">
                <div class="detail-row">
                    <span class="detail-label">Nama Pelanggan</span>
                    <span class="detail-val" id="modalNama">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nomor HP / WhatsApp</span>
                    <span class="detail-val">
                        <span id="modalHP">-</span>
                        <a href="#" id="modalWALink" target="_blank" class="btn btn--ghost" style="padding: 2px 6px; display: inline-flex; align-items: center; gap: 4px; font-size: var(--text-xs); border-color: rgba(255,255,255,0.08); margin-left: 8px;">
                            <i data-lucide="message-square" style="width: 12px; height: 12px; color: #25d366;"></i>
                            Kirim WA
                        </a>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Layanan</span>
                    <span class="detail-val" id="modalLayanan">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Barber</span>
                    <span class="detail-val" id="modalBarber">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal Cukur</span>
                    <span class="detail-val" id="modalTanggal">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Jam Slot</span>
                    <span class="detail-val" id="modalJam">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Harga</span>
                    <span class="detail-val" id="modalHarga" style="color: var(--color-accent); font-weight: 700;">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status Booking</span>
                    <span class="detail-val"><span class="card__badge" id="modalStatus">-</span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status Pembayaran</span>
                    <span class="detail-val"><span class="card__badge" id="modalStatusPembayaran">-</span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Jumlah DP</span>
                    <span class="detail-val" id="modalDP" style="color: var(--color-accent); font-weight: 700;">-</span>
                </div>
                <div class="detail-row" style="flex-direction: column; align-items: flex-start; gap: 5px;">
                    <span class="detail-label">Catatan Pelanggan:</span>
                    <div style="font-size: var(--text-sm); font-style: italic; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 10px; border-radius: var(--radius-sm); width: 100%; word-break: break-all;" id="modalCatatan">
                        -
                    </div>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Waktu Booking Dibuat</span>
                    <span class="detail-val" id="modalDibuat" style="font-weight: normal; color: var(--color-text-muted);">-</span>
                </div>
            </div>
        </div>
        <div class="admin-modal__footer">
            <button class="btn btn--secondary" onclick="closeModal('detailModal')" style="border-color: var(--glass-border);">Tutup</button>
        </div>
    </div>
</div>

<script>
// Parse data and populate modal details
function viewDetail(jsonStr) {
    var data = JSON.parse(jsonStr);
    
    document.getElementById('modalOrderId').innerText = '#' + data.id;
    document.getElementById('modalNama').innerText = data.nama;
    document.getElementById('modalHP').innerText = data.no_hp;
    
    // WA link generator (remove leading 0 and prepend 62)
    var cleanHP = data.no_hp.replace(/^0/, '62');
    document.getElementById('modalWALink').href = 'https://wa.me/' + cleanHP + '?text=Halo%20' + encodeURIComponent(data.nama) + ',%20kami%20dari%20IF%20Barber%20ingin%20mengonfirmasi%20booking%20Anda%20pada%20hari%20' + encodeURIComponent(data.tanggal) + '%20jam%20' + encodeURIComponent(data.jam) + '.';
    
    document.getElementById('modalLayanan').innerText = data.layanan;
    document.getElementById('modalBarber').innerText = data.barber;
    document.getElementById('modalTanggal').innerText = data.tanggal;
    document.getElementById('modalJam').innerText = data.jam + ' WIB';
    document.getElementById('modalHarga').innerText = data.harga;
    document.getElementById('modalDP').innerText = data.dp;
    document.getElementById('modalStatusPembayaran').innerText = data.status_pembayaran;
    
    var payBadge = document.getElementById('modalStatusPembayaran');
    payBadge.className = 'card__badge';
    if (data.status_pembayaran === 'Belum Bayar') payBadge.classList.add('badge--danger');
    else if (data.status_pembayaran === 'Sudah DP') payBadge.classList.add('badge--pending');
    else payBadge.classList.add('badge--success');

    document.getElementById('modalCatatan').innerText = data.catatan;
    document.getElementById('modalDibuat').innerText = data.dibuat;
    
    // Handle badge status styling
    var badge = document.getElementById('modalStatus');
    badge.innerText = data.status;
    badge.className = 'card__badge'; // reset
    if (data.status === 'Pending') badge.classList.add('badge--pending');
    else if (data.status === 'Proses') badge.classList.add('badge--process');
    else if (data.status === 'Selesai') badge.classList.add('badge--success');
    else if (data.status === 'Batal') badge.classList.add('badge--danger');

    openModal('detailModal');
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
