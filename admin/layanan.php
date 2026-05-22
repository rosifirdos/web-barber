<?php
/**
 * IF Barber — Kelola Layanan
 * CRUD Management untuk layanan barbershop
 */

$pageTitle = 'Kelola Layanan — IF Barber';
$headerTitle = 'Kelola Layanan';
$activePage = 'layanan';

include __DIR__ . '/header.php';

// Folder path upload gambar
$uploadDir = BASE_PATH . '/assets/img/';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    // ============================================
    // ACTION: ADD / TAMBAH LAYANAN
    // ============================================
    if ($action === 'add') {
        $nama = sanitize($_POST['nama'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $harga = (float)($_POST['harga'] ?? 0);
        $durasi = (int)($_POST['durasi_menit'] ?? 30);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $errors = [];
        if (empty($nama)) $errors[] = 'Nama layanan wajib diisi.';
        if ($harga <= 0) $errors[] = 'Harga harus lebih besar dari 0.';
        if ($durasi <= 0) $errors[] = 'Durasi harus lebih besar dari 0.';

        // Handle Image Upload
        $gambarName = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['gambar']['tmp_name'];
            $fileName = $_FILES['gambar']['name'];
            $fileSize = $_FILES['gambar']['size'];
            $fileType = $_FILES['gambar']['type'];
            
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                if ($fileSize <= 2 * 1024 * 1024) { // limit 2MB
                    $newFileName = 'service-' . time() . '-' . rand(1000, 9999) . '.' . $fileExtension;
                    
                    // Create directory if not exists
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                        $gambarName = $newFileName;
                    } else {
                        $errors[] = 'Gagal menyimpan gambar di server.';
                    }
                } else {
                    $errors[] = 'Ukuran gambar maksimal adalah 2MB.';
                }
            } else {
                $errors[] = 'Ekstensi gambar tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP.';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO layanan (nama, deskripsi, harga, durasi_menit, gambar, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssdisi', $nama, $deskripsi, $harga, $durasi, $gambarName, $isActive);
            if ($stmt->execute()) {
                setFlash('success', 'Layanan "' . $nama . '" berhasil ditambahkan.');
            } else {
                setFlash('error', 'Gagal menambahkan layanan ke database.');
            }
            $stmt->close();
            redirect(BASE_URL . '/admin/layanan.php');
        } else {
            setFlash('error', implode('<br>', $errors));
        }
    }
    
    // ============================================
    // ACTION: EDIT / UPDATE LAYANAN
    // ============================================
    elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = sanitize($_POST['nama'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $harga = (float)($_POST['harga'] ?? 0);
        $durasi = (int)($_POST['durasi_menit'] ?? 30);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $errors = [];
        if ($id <= 0) $errors[] = 'ID layanan tidak valid.';
        if (empty($nama)) $errors[] = 'Nama layanan wajib diisi.';
        if ($harga <= 0) $errors[] = 'Harga harus lebih besar dari 0.';
        if ($durasi <= 0) $errors[] = 'Durasi harus lebih besar dari 0.';

        // Get current image in case we don't upload a new one
        $imgStmt = $conn->prepare("SELECT gambar FROM layanan WHERE id = ?");
        $imgStmt->bind_param('i', $id);
        $imgStmt->execute();
        $currImg = $imgStmt->get_result()->fetch_assoc()['gambar'] ?? null;
        $imgStmt->close();

        $gambarName = $currImg;

        // Handle New Image Upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['gambar']['tmp_name'];
            $fileName = $_FILES['gambar']['name'];
            $fileSize = $_FILES['gambar']['size'];
            
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                if ($fileSize <= 2 * 1024 * 1024) { // limit 2MB
                    $newFileName = 'service-' . time() . '-' . rand(1000, 9999) . '.' . $fileExtension;

                    if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                        $gambarName = $newFileName;
                        // Delete old image if exists
                        if ($currImg && file_exists($uploadDir . $currImg)) {
                            @unlink($uploadDir . $currImg);
                        }
                    } else {
                        $errors[] = 'Gagal menyimpan gambar baru di server.';
                    }
                } else {
                    $errors[] = 'Ukuran gambar maksimal adalah 2MB.';
                }
            } else {
                $errors[] = 'Ekstensi gambar tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP.';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE layanan SET nama = ?, deskripsi = ?, harga = ?, durasi_menit = ?, gambar = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param('ssdisii', $nama, $deskripsi, $harga, $durasi, $gambarName, $isActive, $id);
            if ($stmt->execute()) {
                setFlash('success', 'Layanan "' . $nama . '" berhasil diperbarui.');
            } else {
                setFlash('error', 'Gagal memperbarui layanan.');
            }
            $stmt->close();
            redirect(BASE_URL . '/admin/layanan.php');
        } else {
            setFlash('error', implode('<br>', $errors));
        }
    }
}

// ============================================
// ACTION: DELETE / TOGGLE ACTIVE (GET)
// ============================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    // Check if service exists
    $checkStmt = $conn->prepare("SELECT id, nama, gambar FROM layanan WHERE id = ?");
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $service = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($service) {
        if ($action === 'delete') {
            // Try to hard-delete
            try {
                $stmt = $conn->prepare("DELETE FROM layanan WHERE id = ?");
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    // Delete image if exists
                    if ($service['gambar'] && file_exists($uploadDir . $service['gambar'])) {
                        @unlink($uploadDir . $service['gambar']);
                    }
                    setFlash('success', 'Layanan "' . $service['nama'] . '" berhasil dihapus secara permanen.');
                }
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                // If foreign key constraint fails, soft-delete it!
                $stmt = $conn->prepare("UPDATE layanan SET is_active = 0 WHERE id = ?");
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    setFlash('warning', 'Layanan "' . $service['nama'] . '" sedang digunakan dalam riwayat booking. Layanan telah dinonaktifkan (soft delete) agar riwayat tetap terjaga.');
                } else {
                    setFlash('error', 'Gagal menghapus atau menonaktifkan layanan.');
                }
                $stmt->close();
            }
        } elseif ($action === 'toggle') {
            // Toggle active status
            $stmt = $conn->prepare("UPDATE layanan SET is_active = NOT is_active WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                setFlash('success', 'Status layanan berhasil diubah.');
            }
            $stmt->close();
        }
    }
    redirect(BASE_URL . '/admin/layanan.php');
}

// Fetch all services
$query = "SELECT * FROM layanan ORDER BY is_active DESC, harga ASC";
$res = $conn->query($query);
$servicesList = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<!-- ============================================
     HEADER & ADD ACTION
     ============================================ -->
<div style="display: flex; justify-content: flex-end; margin-bottom: var(--space-lg);">
    <button class="btn btn--primary" onclick="openAddModal()">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
        Tambah Layanan Baru
    </button>
</div>

<!-- ============================================
     SERVICES TABLE
     ============================================ -->
<div class="admin-card">
    <div class="admin-card__header">
        <h3 class="admin-card__title">
            <i data-lucide="scissors"></i>
            Daftar Layanan Barbershop
        </h3>
    </div>
    <div class="admin-card__body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Gambar</th>
                        <th>Nama Layanan</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th style="width: 150px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($servicesList)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 40px;">
                                Belum ada layanan terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($servicesList as $s): 
                            $statusClass = $s['is_active'] ? 'badge--success' : 'badge--danger';
                            $statusText = $s['is_active'] ? 'Aktif' : 'Nonaktif';
                            $imgSrc = $s['gambar'] ? BASE_URL . '/assets/img/' . $s['gambar'] : 'https://placehold.co/100x100?text=Service';

                            // Format JSON for edit modal
                            $jsonStr = htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td>
                                    <div style="width: 60px; height: 60px; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--glass-border);">
                                        <img src="<?= $imgSrc ?>" alt="<?= e($s['nama']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600; display: block;"><?= e($s['nama']) ?></span>
                                </td>
                                <td>
                                    <div style="max-width: 320px; font-size: var(--text-xs); color: var(--color-text-muted); overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                        <?= e($s['deskripsi']) ?>
                                    </div>
                                </td>
                                <td style="font-weight: 600; color: var(--color-accent);"><?= formatRupiah($s['harga']) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 4px; font-size: var(--text-xs);">
                                        <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                                        <?= $s['durasi_menit'] ?> menit
                                    </div>
                                </td>
                                <td>
                                    <a href="?action=toggle&id=<?= $s['id'] ?>" class="card__badge <?= $statusClass ?>" style="text-decoration: none;" title="Klik untuk mengubah status">
                                        <?= $statusText ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: center;">
                                        <!-- Edit -->
                                        <button class="btn-action btn-action--edit" onclick="openEditModal('<?= $jsonStr ?>')" title="Edit Layanan">
                                            <i data-lucide="edit-3" style="width: 15px; height: 15px;"></i>
                                        </button>

                                        <!-- Delete -->
                                        <button class="btn-action btn-action--delete" onclick="confirmAction('Apakah Anda yakin ingin menghapus layanan &quot;<?= e($s['nama']) ?>&quot;? Jika sudah ada pesanan terkait, layanan ini akan dinonaktifkan.', '?action=delete&id=<?= $s['id'] ?>')" title="Hapus Layanan">
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
     MODAL: TAMBAH & EDIT LAYANAN
     ============================================ -->
<div class="admin-modal" id="layananModal">
    <div class="admin-modal__overlay"></div>
    <div class="admin-modal__container">
        <form id="layananForm" method="POST" action="" enctype="multipart/form-data">
            <!-- Hidden Fields -->
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="formId" value="">

            <div class="admin-modal__header">
                <h3 class="admin-modal__title" id="modalTitle">Tambah Layanan Baru</h3>
                <button type="button" class="admin-modal__close">×</button>
            </div>
            
            <div class="admin-modal__body">
                <!-- Nama Layanan -->
                <div class="form-group">
                    <label class="form-label" for="inputNama">Nama Layanan *</label>
                    <input type="text" name="nama" id="inputNama" class="form-input" placeholder="Contoh: Premium Haircut" required>
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label class="form-label" for="inputDeskripsi">Deskripsi</label>
                    <textarea name="deskripsi" id="inputDeskripsi" class="form-textarea" rows="3" placeholder="Jelaskan detail dari layanan ini..."></textarea>
                </div>

                <div class="admin-form-grid">
                    <!-- Harga -->
                    <div class="form-group">
                        <label class="form-label" for="inputHarga">Harga (Rp) *</label>
                        <input type="number" name="harga" id="inputHarga" class="form-input" min="0" step="500" placeholder="Contoh: 35000" required>
                    </div>

                    <!-- Durasi -->
                    <div class="form-group">
                        <label class="form-label" for="inputDurasi">Durasi (Menit) *</label>
                        <input type="number" name="durasi_menit" id="inputDurasi" class="form-input" min="5" max="300" step="5" placeholder="Contoh: 30" required>
                    </div>
                </div>

                <!-- Status Aktif -->
                <div class="form-group">
                    <label class="form-switch">
                        <input type="checkbox" name="is_active" id="inputIsActive" class="form-switch__input" checked>
                        <span class="form-switch__slider"></span>
                        <span class="form-label" style="margin-bottom: 0;">Layanan Aktif (Tampilkan di form booking pelanggan)</span>
                    </label>
                </div>

                <!-- Upload Gambar -->
                <div class="form-group">
                    <label class="form-label">Gambar Layanan</label>
                    <div class="image-preview-box" onclick="document.getElementById('inputGambar').click()">
                        <img src="" id="gambarPreview" style="display: none;">
                        <div class="image-preview-placeholder" id="gambarPlaceholder">
                            <i data-lucide="image" style="width: 32px; height: 32px;"></i>
                            <span>Pilih gambar layanan (JPG/PNG/WEBP, maks. 2MB)</span>
                        </div>
                    </div>
                    <input type="file" name="gambar" id="inputGambar" style="display: none;" accept="image/*">
                </div>
            </div>

            <div class="admin-modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('layananModal')" style="border-color: var(--glass-border);">Batal</button>
                <button type="submit" class="btn btn--primary" id="btnSubmit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize previews
document.addEventListener('DOMContentLoaded', function() {
    initImagePreview('inputGambar', 'gambarPreview', 'gambarPlaceholder');
});

function openAddModal() {
    // Reset Form fields
    document.getElementById('layananForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('formId').value = '';
    document.getElementById('modalTitle').innerText = 'Tambah Layanan Baru';
    
    // Reset Image Previews
    document.getElementById('gambarPreview').style.display = 'none';
    document.getElementById('gambarPreview').src = '';
    document.getElementById('gambarPlaceholder').style.display = 'flex';
    
    openModal('layananModal');
}

function openEditModal(jsonStr) {
    var data = JSON.parse(jsonStr);
    
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formId').value = data.id;
    document.getElementById('modalTitle').innerText = 'Edit Layanan: ' + data.nama;
    
    document.getElementById('inputNama').value = data.nama;
    document.getElementById('inputDeskripsi').value = data.deskripsi || '';
    document.getElementById('inputHarga').value = Math.floor(data.harga);
    document.getElementById('inputDurasi').value = data.durasi_menit;
    document.getElementById('inputIsActive').checked = (parseInt(data.is_active) === 1);
    
    // Set image preview if exists
    var preview = document.getElementById('gambarPreview');
    var placeholder = document.getElementById('gambarPlaceholder');
    
    if (data.gambar) {
        preview.src = '<?= BASE_URL ?>/assets/img/' + data.gambar;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    } else {
        preview.style.display = 'none';
        preview.src = '';
        placeholder.style.display = 'flex';
    }
    
    openModal('layananModal');
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
