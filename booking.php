<?php
/**
 * IF Barber — Booking Page
 * Form pemesanan multi-step untuk pelanggan
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch data
$layananList = getLayananAktif($conn);
$barberList = getBarberAktif($conn);

// Pre-select barber if passed via URL
$preselectedBarber = isset($_GET['barber']) ? (int)$_GET['barber'] : null;

// Handle form submission
$errors = [];
$success = false;
$bookingId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $nama = sanitize($_POST['nama_pelanggan'] ?? '');
    $noHp = sanitize($_POST['no_hp'] ?? '');
    $layananId = (int)($_POST['layanan_id'] ?? 0);
    $barberId = (int)($_POST['barber_id'] ?? 0);
    $tanggal = sanitize($_POST['tanggal'] ?? '');
    $jam = sanitize($_POST['jam'] ?? '');
    $catatan = sanitize($_POST['catatan'] ?? '');

    // Validation
    if (empty($nama)) $errors[] = 'Nama lengkap wajib diisi.';
    if (empty($noHp)) $errors[] = 'Nomor HP wajib diisi.';
    if (!preg_match('/^[0-9]{10,15}$/', $noHp)) $errors[] = 'Format nomor HP tidak valid.';
    if ($layananId <= 0) $errors[] = 'Silakan pilih layanan.';
    if ($barberId <= 0) $errors[] = 'Silakan pilih barber.';
    if (empty($tanggal)) $errors[] = 'Silakan pilih tanggal.';
    if (empty($jam)) $errors[] = 'Silakan pilih jam.';

    // Check date validity
    if (!empty($tanggal)) {
        $bookDate = strtotime($tanggal);
        $today = strtotime(date('Y-m-d'));
        if ($bookDate < $today) $errors[] = 'Tanggal tidak boleh di masa lalu.';
        if (isHariLibur($tanggal)) $errors[] = 'Maaf, barbershop tutup di hari tersebut.';
    }

    // Check slot availability
    if (empty($errors) && !empty($tanggal) && !empty($jam)) {
        $bookedSlots = getBookedSlots($conn, $barberId, $tanggal);
        if (in_array($jam, $bookedSlots)) {
            $errors[] = 'Jam tersebut sudah terisi. Silakan pilih jam lain.';
        }
    }

    // Get layanan price
    $totalHarga = 0;
    if ($layananId > 0) {
        $stmt = $conn->prepare("SELECT harga FROM layanan WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $layananId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $totalHarga = $row['harga'];
        }
        $stmt->close();
    }

    // Insert booking
    if (empty($errors)) {
        $memberId = isMemberLoggedIn() ? $_SESSION['member_id'] : null;

        $stmt = $conn->prepare("INSERT INTO booking (nama_pelanggan, no_hp, layanan_id, barber_id, tanggal, jam, status, member_id, total_harga, catatan) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?)");
        $stmt->bind_param('ssiissids', $nama, $noHp, $layananId, $barberId, $tanggal, $jam, $memberId, $totalHarga, $catatan);

        if ($stmt->execute()) {
            $bookingId = $stmt->insert_id;
            $success = true;
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan booking. Silakan coba lagi.';
        }
        $stmt->close();
    }
}

// Barber photos mapping
$barberPhotos = [
    1 => 'barber-reza.png',
    2 => 'barber-dimas.png',
    3 => 'barber-arief.png',
    4 => 'barber-bayu.png'
];

$pageTitle = 'Booking — ' . APP_NAME;
$pageDesc = 'Booking jadwal potong rambut online di IF Barber. Pilih layanan, barber, dan waktu yang sesuai.';
$activePage = 'booking';

include __DIR__ . '/includes/header.php';
?>

<!-- Booking Page Styles -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/booking.css">

<!-- ============================================
     BOOKING PAGE
     ============================================ -->
<section class="booking-page">
    <div class="container">

        <?php if ($success && $bookingId): ?>
        <!-- ============================================
             SUCCESS STATE
             ============================================ -->
        <div class="booking-success animate-on-scroll visible">
            <div class="booking-success__icon">
                <svg viewBox="0 0 52 52" class="checkmark">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <h2 class="booking-success__title">Booking Berhasil!</h2>
            <p class="booking-success__text">
                Reservasi Anda telah tercatat. Silakan datang sesuai jadwal yang dipilih.
            </p>

            <div class="booking-receipt glass">
                <div class="booking-receipt__header">
                    <span class="booking-receipt__brand">IF Barber</span>
                    <span class="booking-receipt__id">#<?= str_pad($bookingId, 4, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="booking-receipt__divider"></div>
                <div class="booking-receipt__row">
                    <span>Nama</span>
                    <strong><?= e($nama) ?></strong>
                </div>
                <div class="booking-receipt__row">
                    <span>No. HP</span>
                    <strong><?= e($noHp) ?></strong>
                </div>
                <div class="booking-receipt__row">
                    <span>Tanggal</span>
                    <strong><?= formatTanggal($tanggal) ?></strong>
                </div>
                <div class="booking-receipt__row">
                    <span>Jam</span>
                    <strong><?= e($jam) ?> WIB</strong>
                </div>
                <div class="booking-receipt__row">
                    <span>Status</span>
                    <span class="card__badge badge--pending">Pending</span>
                </div>
                <div class="booking-receipt__divider"></div>
                <div class="booking-receipt__row booking-receipt__total">
                    <span>Total</span>
                    <strong><?= formatRupiah($totalHarga) ?></strong>
                </div>
            </div>

            <div class="booking-success__actions">
                <a href="<?= BASE_URL ?>" class="btn btn--secondary">
                    <i data-lucide="home" style="width:16px;height:16px;"></i>
                    Kembali ke Home
                </a>
                <a href="<?= BASE_URL ?>/booking.php" class="btn btn--primary">
                    <i data-lucide="calendar-plus" style="width:16px;height:16px;"></i>
                    Booking Lagi
                </a>
            </div>
        </div>

        <?php else: ?>
        <!-- ============================================
             BOOKING FORM
             ============================================ -->
        <div class="booking-header animate-on-scroll visible">
            <span class="section__label">Booking</span>
            <h1 class="section__title">Reservasi Online</h1>
            <p class="section__subtitle">
                Pilih layanan, barber, dan jadwal yang Anda inginkan. Proses cepat dan mudah tanpa perlu antre.
            </p>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
        <div class="booking-errors glass">
            <div class="booking-errors__icon">
                <i data-lucide="alert-circle"></i>
            </div>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Step Indicators -->
        <div class="booking-steps animate-on-scroll visible">
            <div class="booking-step active" data-step="1">
                <div class="booking-step__number">1</div>
                <span class="booking-step__label">Layanan</span>
            </div>
            <div class="booking-step__line"></div>
            <div class="booking-step" data-step="2">
                <div class="booking-step__number">2</div>
                <span class="booking-step__label">Barber</span>
            </div>
            <div class="booking-step__line"></div>
            <div class="booking-step" data-step="3">
                <div class="booking-step__number">3</div>
                <span class="booking-step__label">Jadwal</span>
            </div>
            <div class="booking-step__line"></div>
            <div class="booking-step" data-step="4">
                <div class="booking-step__number">4</div>
                <span class="booking-step__label">Konfirmasi</span>
            </div>
        </div>

        <form method="POST" action="" class="booking-form" id="bookingForm">

            <!-- ==========================================
                 STEP 1: Pilih Layanan
                 ========================================== -->
            <div class="booking-panel active" id="step1">
                <h3 class="booking-panel__title">
                    <i data-lucide="scissors" style="width:22px;height:22px;color:var(--color-accent);"></i>
                    Pilih Layanan
                </h3>
                <p class="booking-panel__subtitle">Pilih layanan grooming yang Anda inginkan</p>

                <div class="service-select-grid">
                    <?php foreach ($layananList as $layanan): ?>
                    <label class="service-select-card glass" data-price="<?= $layanan['harga'] ?>" data-duration="<?= $layanan['durasi_menit'] ?>" data-name="<?= e($layanan['nama']) ?>">
                        <input type="radio" name="layanan_id" value="<?= $layanan['id'] ?>" class="service-select-card__radio" required>
                        <div class="service-select-card__content">
                            <div class="service-select-card__header">
                                <h4 class="service-select-card__name"><?= e($layanan['nama']) ?></h4>
                                <span class="service-select-card__check">
                                    <i data-lucide="check" style="width:16px;height:16px;"></i>
                                </span>
                            </div>
                            <p class="service-select-card__desc"><?= e($layanan['deskripsi']) ?></p>
                            <div class="service-select-card__footer">
                                <span class="service-select-card__price"><?= formatRupiah($layanan['harga']) ?></span>
                                <span class="service-select-card__duration">
                                    <i data-lucide="clock" style="width:13px;height:13px;"></i>
                                    <?= $layanan['durasi_menit'] ?> menit
                                </span>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="booking-panel__actions">
                    <a href="<?= BASE_URL ?>" class="btn btn--ghost">Kembali</a>
                    <button type="button" class="btn btn--primary" onclick="goToStep(2)" id="btnStep1Next" disabled>
                        Lanjut — Pilih Barber
                        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 STEP 2: Pilih Barber
                 ========================================== -->
            <div class="booking-panel" id="step2">
                <h3 class="booking-panel__title">
                    <i data-lucide="user" style="width:22px;height:22px;color:var(--color-accent);"></i>
                    Pilih Barber
                </h3>
                <p class="booking-panel__subtitle">Pilih barber profesional yang Anda inginkan</p>

                <div class="barber-select-grid">
                    <?php foreach ($barberList as $barber): ?>
                    <label class="barber-select-card glass" data-name="<?= e($barber['nama']) ?>">
                        <input type="radio" name="barber_id" value="<?= $barber['id'] ?>" class="barber-select-card__radio" required
                            <?= ($preselectedBarber === $barber['id']) ? 'checked' : '' ?>>
                        <div class="barber-select-card__content">
                            <div class="barber-select-card__avatar">
                                <img src="<?= BASE_URL ?>/assets/img/<?= $barberPhotos[$barber['id']] ?? 'barber-reza.png' ?>" alt="<?= e($barber['nama']) ?>">
                            </div>
                            <div class="barber-select-card__info">
                                <h4 class="barber-select-card__name"><?= e($barber['nama']) ?></h4>
                                <p class="barber-select-card__spec"><?= e($barber['spesialisasi']) ?></p>
                            </div>
                            <span class="barber-select-card__check">
                                <i data-lucide="check" style="width:16px;height:16px;"></i>
                            </span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="booking-panel__actions">
                    <button type="button" class="btn btn--ghost" onclick="goToStep(1)">
                        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
                        Kembali
                    </button>
                    <button type="button" class="btn btn--primary" onclick="goToStep(3)" id="btnStep2Next" disabled>
                        Lanjut — Pilih Jadwal
                        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 STEP 3: Pilih Jadwal
                 ========================================== -->
            <div class="booking-panel" id="step3">
                <h3 class="booking-panel__title">
                    <i data-lucide="calendar" style="width:22px;height:22px;color:var(--color-accent);"></i>
                    Pilih Jadwal
                </h3>
                <p class="booking-panel__subtitle">Pilih tanggal dan jam yang tersedia</p>

                <div class="schedule-grid">
                    <div class="form-group">
                        <label class="form-label" for="inputTanggal">Tanggal</label>
                        <input type="date" name="tanggal" id="inputTanggal" class="form-input"
                               min="<?= date('Y-m-d') ?>"
                               max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                               required>
                        <span class="form-hint">Pilih tanggal dalam 30 hari ke depan. Hari Minggu libur.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jam Tersedia</label>
                        <div class="timeslot-grid" id="timeslotGrid">
                            <div class="timeslot-empty">
                                <i data-lucide="calendar-days" style="width:32px;height:32px;opacity:0.3;"></i>
                                <p>Pilih tanggal terlebih dahulu untuk melihat jam yang tersedia</p>
                            </div>
                        </div>
                        <input type="hidden" name="jam" id="inputJam" required>
                    </div>
                </div>

                <div class="timeslot-legend">
                    <div class="timeslot-legend__item">
                        <span class="timeslot-legend__dot timeslot-legend__dot--available"></span>
                        Tersedia
                    </div>
                    <div class="timeslot-legend__item">
                        <span class="timeslot-legend__dot timeslot-legend__dot--booked"></span>
                        Terisi
                    </div>
                    <div class="timeslot-legend__item">
                        <span class="timeslot-legend__dot timeslot-legend__dot--selected"></span>
                        Dipilih
                    </div>
                </div>

                <div class="booking-panel__actions">
                    <button type="button" class="btn btn--ghost" onclick="goToStep(2)">
                        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
                        Kembali
                    </button>
                    <button type="button" class="btn btn--primary" onclick="goToStep(4)" id="btnStep3Next" disabled>
                        Lanjut — Konfirmasi
                        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 STEP 4: Konfirmasi & Data Diri
                 ========================================== -->
            <div class="booking-panel" id="step4">
                <h3 class="booking-panel__title">
                    <i data-lucide="clipboard-check" style="width:22px;height:22px;color:var(--color-accent);"></i>
                    Konfirmasi Booking
                </h3>
                <p class="booking-panel__subtitle">Lengkapi data diri dan periksa kembali pesanan Anda</p>

                <div class="confirm-grid">
                    <!-- Data Diri -->
                    <div class="confirm-form">
                        <h4 class="confirm-section-title">Data Diri</h4>

                        <div class="form-group">
                            <label class="form-label" for="inputNama">Nama Lengkap *</label>
                            <input type="text" name="nama_pelanggan" id="inputNama" class="form-input"
                                   placeholder="Masukkan nama lengkap Anda"
                                   value="<?= isMemberLoggedIn() ? e($_SESSION['member_nama']) : '' ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="inputHP">Nomor HP (WhatsApp) *</label>
                            <input type="tel" name="no_hp" id="inputHP" class="form-input"
                                   placeholder="contoh: 081234567890"
                                   value="<?= isMemberLoggedIn() ? e($_SESSION['member_hp']) : '' ?>"
                                   pattern="[0-9]{10,15}" required>
                            <span class="form-hint">Nomor aktif untuk konfirmasi via WhatsApp</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="inputCatatan">Catatan (Opsional)</label>
                            <textarea name="catatan" id="inputCatatan" class="form-textarea" rows="3"
                                      placeholder="Contoh: Minta model seperti di foto referensi..."></textarea>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="confirm-summary glass">
                        <h4 class="confirm-section-title">Ringkasan Booking</h4>

                        <div class="confirm-summary__row">
                            <span class="confirm-summary__label">Layanan</span>
                            <span class="confirm-summary__value" id="summaryLayanan">-</span>
                        </div>
                        <div class="confirm-summary__row">
                            <span class="confirm-summary__label">Barber</span>
                            <span class="confirm-summary__value" id="summaryBarber">-</span>
                        </div>
                        <div class="confirm-summary__row">
                            <span class="confirm-summary__label">Tanggal</span>
                            <span class="confirm-summary__value" id="summaryTanggal">-</span>
                        </div>
                        <div class="confirm-summary__row">
                            <span class="confirm-summary__label">Jam</span>
                            <span class="confirm-summary__value" id="summaryJam">-</span>
                        </div>
                        <div class="confirm-summary__row">
                            <span class="confirm-summary__label">Durasi</span>
                            <span class="confirm-summary__value" id="summaryDurasi">-</span>
                        </div>

                        <div class="confirm-summary__divider"></div>

                        <div class="confirm-summary__row confirm-summary__total">
                            <span class="confirm-summary__label">Total</span>
                            <span class="confirm-summary__value" id="summaryTotal">-</span>
                        </div>

                        <button type="submit" class="btn btn--primary btn--block btn--lg" id="btnSubmit">
                            <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
                            Konfirmasi Booking
                        </button>

                        <p class="confirm-summary__note">
                            <i data-lucide="info" style="width:13px;height:13px;"></i>
                            Pembayaran dilakukan di tempat setelah layanan selesai.
                        </p>
                    </div>
                </div>

                <div class="booking-panel__actions">
                    <button type="button" class="btn btn--ghost" onclick="goToStep(3)">
                        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
                        Kembali
                    </button>
                </div>
            </div>

        </form>
        <?php endif; ?>

    </div>
</section>

<!-- All time slots data (for JS) -->
<script>
    var ALL_TIMESLOTS = <?= json_encode(generateTimeSlots()) ?>;
    var BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/assets/js/booking.js?v=<?= time() ?>"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
