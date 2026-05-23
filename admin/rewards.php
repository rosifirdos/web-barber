<?php
/**
 * IF Barber — Kelola Rewards
 * CRUD & Manajemen Rewards untuk membership
 */

$pageTitle = 'Kelola Rewards — IF Barber';
$headerTitle = 'Kelola Rewards';
$activePage = 'rewards';

include __DIR__ . '/header.php';

// Handle Action (Hapus / Toggle Status)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'delete') {
        // Cek apakah sudah pernah di-claim
        $check = $conn->prepare("SELECT COUNT(*) as cnt FROM reward_claims WHERE reward_id = ?");
        $check->bind_param('i', $id);
        $check->execute();
        $hasClaims = $check->get_result()->fetch_assoc()['cnt'] > 0;
        $check->close();

        if ($hasClaims) {
            setFlash('error', 'Reward tidak bisa dihapus karena sudah pernah ditukarkan oleh member. Gunakan fitur Nonaktifkan.');
        } else {
            $stmt = $conn->prepare("DELETE FROM rewards WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                setFlash('success', 'Reward berhasil dihapus.');
            } else {
                setFlash('error', 'Gagal menghapus reward.');
            }
            $stmt->close();
        }
    } elseif ($action === 'toggle') {
        $stmt = $conn->prepare("UPDATE rewards SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            setFlash('success', 'Status reward berhasil diubah.');
        } else {
            setFlash('error', 'Gagal mengubah status reward.');
        }
        $stmt->close();
    }
    redirect(BASE_URL . '/admin/rewards.php');
}

// Handle Tambah / Edit Reward
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $poin = (int)($_POST['poin_diperlukan'] ?? 0);
    $jenis = $_POST['jenis'] ?? 'Diskon';
    $icon = trim($_POST['icon'] ?? 'gift');
    
    // Handle jenis spesifik
    $nilaiDiskon = NULL;
    $layananId = NULL;
    
    if ($jenis === 'Diskon') {
        $nilaiDiskon = isset($_POST['nilai_diskon']) ? (float)$_POST['nilai_diskon'] : 0;
    } elseif ($jenis === 'Layanan Gratis') {
        $layananId = isset($_POST['layanan_id']) ? (int)$_POST['layanan_id'] : NULL;
    }

    if (empty($nama) || $poin <= 0) {
        setFlash('error', 'Nama dan Poin Diperlukan wajib diisi.');
    } else {
        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE rewards SET nama=?, deskripsi=?, poin_diperlukan=?, jenis=?, nilai_diskon=?, layanan_id=?, icon=? WHERE id=?");
            $stmt->bind_param('ssisdisi', $nama, $deskripsi, $poin, $jenis, $nilaiDiskon, $layananId, $icon, $id);
            if ($stmt->execute()) {
                setFlash('success', 'Reward berhasil diupdate.');
            } else {
                setFlash('error', 'Gagal mengupdate reward.');
            }
            $stmt->close();
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO rewards (nama, deskripsi, poin_diperlukan, jenis, nilai_diskon, layanan_id, icon) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssisdis', $nama, $deskripsi, $poin, $jenis, $nilaiDiskon, $layananId, $icon);
            if ($stmt->execute()) {
                setFlash('success', 'Reward baru berhasil ditambahkan.');
            } else {
                setFlash('error', 'Gagal menambahkan reward.');
            }
            $stmt->close();
        }
    }
    redirect(BASE_URL . '/admin/rewards.php');
}

// Fetch Data Rewards
$rewards = getAllRewards($conn);

// Fetch Data Layanan (untuk dropdown "Layanan Gratis")
$layananQuery = $conn->query("SELECT id, nama FROM layanan WHERE status = 'Aktif' ORDER BY nama ASC");
$layananOptions = $layananQuery->fetch_all(MYSQLI_ASSOC);

?>

<div class="admin-card">
    <div class="admin-card__header">
        <h3 class="admin-card__title">
            <i data-lucide="gift"></i>
            Daftar Rewards
        </h3>
        <button class="btn btn--primary" onclick="openRewardModal()">
            <i data-lucide="plus" style="width: 16px; height: 16px; margin-right: 5px;"></i> Tambah Reward
        </button>
    </div>
    <div class="admin-card__body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Icon</th>
                        <th>Nama Reward</th>
                        <th>Poin</th>
                        <th>Jenis</th>
                        <th>Detail</th>
                        <th>Status</th>
                        <th style="width: 120px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rewards)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 40px;">
                                Belum ada reward terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rewards as $r): 
                            // Encode for JS
                            $jsonStr = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td style="text-align: center;">
                                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(212,175,55,0.15); display: inline-flex; align-items: center; justify-content: center;">
                                        <i data-lucide="<?= e($r['icon'] ?: 'gift') ?>" style="width: 18px; height: 18px; color: var(--color-accent);"></i>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?= e($r['nama']) ?></div>
                                    <div style="font-size: var(--text-xs); color: var(--color-text-muted); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($r['deskripsi']) ?></div>
                                </td>
                                <td style="font-weight: 700; color: var(--color-accent);"><?= number_format($r['poin_diperlukan']) ?></td>
                                <td><?= e($r['jenis']) ?></td>
                                <td>
                                    <?php if ($r['jenis'] === 'Diskon'): ?>
                                        Diskon <?= (float)$r['nilai_diskon'] ?>%
                                    <?php elseif ($r['jenis'] === 'Layanan Gratis'): ?>
                                        Layanan: <?= e($r['nama_layanan'] ?? 'Tidak ditemukan') ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['is_active']): ?>
                                        <span class="card__badge badge--success">Aktif</span>
                                    <?php else: ?>
                                        <span class="card__badge badge--danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: center;">
                                        <!-- Edit -->
                                        <button class="btn-action btn-action--process" onclick="editReward('<?= $jsonStr ?>')" title="Edit Reward">
                                            <i data-lucide="edit-3" style="width: 15px; height: 15px;"></i>
                                        </button>
                                        <!-- Toggle Status -->
                                        <a href="?action=toggle&id=<?= $r['id'] ?>" class="btn-action <?= $r['is_active'] ? 'btn-action--delete' : 'btn-action--success' ?>" title="<?= $r['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <i data-lucide="<?= $r['is_active'] ? 'eye-off' : 'eye' ?>" style="width: 15px; height: 15px;"></i>
                                        </a>
                                        <!-- Delete -->
                                        <button class="btn-action btn-action--delete" onclick="confirmAction('Yakin ingin menghapus reward ini?', '?action=delete&id=<?= $r['id'] ?>')" title="Hapus Permanen">
                                            <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                                        </button>
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
     MODAL: FORM REWARD
     ============================================ -->
<div class="admin-modal" id="rewardModal">
    <div class="admin-modal__overlay"></div>
    <div class="admin-modal__container" style="max-width: 500px;">
        <form method="POST" action="">
            <div class="admin-modal__header">
                <h3 class="admin-modal__title" id="modalTitle">Tambah Reward Baru</h3>
                <button type="button" class="admin-modal__close" onclick="closeModal('rewardModal')">×</button>
            </div>
            <div class="admin-modal__body">
                <input type="hidden" name="id" id="reward_id" value="0">
                
                <div class="form-group">
                    <label>Nama Reward *</label>
                    <input type="text" name="nama" id="reward_nama" class="form-input" required placeholder="Contoh: Diskon 20%">
                </div>

                <div class="form-group">
                    <label>Deskripsi (S&K)</label>
                    <textarea name="deskripsi" id="reward_deskripsi" class="form-input" rows="3" placeholder="Contoh: Potongan 20% untuk semua layanan. Berlaku 30 hari."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label>Poin Diperlukan *</label>
                        <input type="number" name="poin_diperlukan" id="reward_poin" class="form-input" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Icon (Lucide)</label>
                        <input type="text" name="icon" id="reward_icon" class="form-input" value="gift" placeholder="gift, percent, scissors...">
                    </div>
                </div>

                <div class="form-group">
                    <label>Jenis Reward *</label>
                    <select name="jenis" id="reward_jenis" class="form-input" required onchange="toggleJenisFields()">
                        <option value="Diskon">Diskon Persen (%)</option>
                        <option value="Layanan Gratis">Layanan Gratis</option>
                        <option value="Voucher">Voucher / Lainnya</option>
                    </select>
                </div>

                <!-- Field Khusus Diskon -->
                <div class="form-group" id="field_diskon" style="display: block;">
                    <label>Nilai Diskon (%)</label>
                    <input type="number" name="nilai_diskon" id="reward_nilai_diskon" class="form-input" min="1" max="100" step="0.01" placeholder="Contoh: 10">
                </div>

                <!-- Field Khusus Layanan -->
                <div class="form-group" id="field_layanan" style="display: none;">
                    <label>Pilih Layanan</label>
                    <select name="layanan_id" id="reward_layanan_id" class="form-input">
                        <option value="">-- Pilih Layanan --</option>
                        <?php foreach ($layananOptions as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= e($l['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
            <div class="admin-modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('rewardModal')" style="border-color: var(--glass-border);">Batal</button>
                <button type="submit" class="btn btn--primary">Simpan Reward</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleJenisFields() {
    var jenis = document.getElementById('reward_jenis').value;
    document.getElementById('field_diskon').style.display = (jenis === 'Diskon') ? 'block' : 'none';
    document.getElementById('field_layanan').style.display = (jenis === 'Layanan Gratis') ? 'block' : 'none';
}

function openRewardModal() {
    document.getElementById('modalTitle').innerText = 'Tambah Reward Baru';
    document.getElementById('reward_id').value = '0';
    document.getElementById('reward_nama').value = '';
    document.getElementById('reward_deskripsi').value = '';
    document.getElementById('reward_poin').value = '';
    document.getElementById('reward_icon').value = 'gift';
    document.getElementById('reward_jenis').value = 'Diskon';
    document.getElementById('reward_nilai_diskon').value = '';
    document.getElementById('reward_layanan_id').value = '';
    toggleJenisFields();
    openModal('rewardModal');
}

function editReward(jsonStr) {
    var data = JSON.parse(jsonStr);
    document.getElementById('modalTitle').innerText = 'Edit Reward';
    document.getElementById('reward_id').value = data.id;
    document.getElementById('reward_nama').value = data.nama;
    document.getElementById('reward_deskripsi').value = data.deskripsi;
    document.getElementById('reward_poin').value = data.poin_diperlukan;
    document.getElementById('reward_icon').value = data.icon || 'gift';
    document.getElementById('reward_jenis').value = data.jenis;
    document.getElementById('reward_nilai_diskon').value = data.nilai_diskon;
    document.getElementById('reward_layanan_id').value = data.layanan_id;
    toggleJenisFields();
    openModal('rewardModal');
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
