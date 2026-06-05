<?php
/**
 * IF Barber — Simulasi Pembayaran
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookingId <= 0) {
    header("Location: " . BASE_URL);
    exit;
}

// Fetch booking data
$stmt = $conn->prepare("
    SELECT b.*, l.nama AS nama_layanan, br.nama AS nama_barber
    FROM booking b
    LEFT JOIN layanan l ON b.layanan_id = l.id
    LEFT JOIN barber br ON b.barber_id = br.id
    WHERE b.id = ?
");
$stmt->bind_param('i', $bookingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . BASE_URL);
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

// Check if expired
$now = new DateTime();
$expired = new DateTime($booking['waktu_expired']);

if ($booking['status'] === 'Pending Payment' && $now > $expired) {
    // Update to Expired
    $conn->query("UPDATE booking SET status = 'Expired', status_pembayaran = 'Belum Bayar' WHERE id = " . $bookingId);
    $booking['status'] = 'Expired';
}

// Handle simulasi pembayaran success
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulate_payment'])) {
    if ($booking['status'] === 'Pending Payment') {
        $stmtUpdate = $conn->prepare("UPDATE booking SET status = 'Confirmed', status_pembayaran = 'Sudah DP', metode_pembayaran = 'QRIS' WHERE id = ?");
        $stmtUpdate->bind_param('i', $bookingId);
        $stmtUpdate->execute();
        $stmtUpdate->close();
        
        // Refresh page
        header("Location: " . BASE_URL . "/pembayaran.php?id=" . $bookingId . "&success=1");
        exit;
    }
}

$pageTitle = 'Pembayaran — ' . APP_NAME;
$activePage = 'booking';
include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/booking.css">

<style>
.payment-container {
    max-width: 600px;
    margin: 4rem auto;
    padding: 0 1rem;
}
.payment-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
}
.timer {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-accent);
    margin: 1rem 0;
}
.payment-details {
    text-align: left;
    margin: 2rem 0;
    padding: 1.5rem;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 12px;
}
.payment-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}
.payment-row:last-child {
    margin-bottom: 0;
}
.payment-row.total {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    font-weight: 700;
    font-size: 1.25rem;
    color: var(--color-accent);
}
.qris-dummy {
    width: 200px;
    height: 200px;
    background: #fff;
    margin: 1.5rem auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    padding: 10px;
}
.qris-dummy img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    color: #10b981;
}
.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: #ef4444;
}
</style>

<section class="payment-container">
    <div class="payment-card glass animate-on-scroll visible">
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i data-lucide="check-circle"></i>
                <div>
                    <strong>Pembayaran Berhasil!</strong><br>
                    DP telah dibayarkan. Jadwal Anda sudah dikonfirmasi.
                </div>
            </div>
            
            <a href="<?= BASE_URL ?>" class="btn btn--secondary mt-4">Kembali ke Home</a>
        
        <?php elseif ($booking['status'] === 'Expired'): ?>
            <div class="alert alert-danger">
                <i data-lucide="x-circle"></i>
                <div>
                    <strong>Waktu Pembayaran Habis</strong><br>
                    Booking Anda telah dibatalkan secara otomatis.
                </div>
            </div>
            
            <a href="<?= BASE_URL ?>/booking.php" class="btn btn--primary mt-4">Booking Ulang</a>
            
        <?php elseif ($booking['status'] === 'Confirmed'): ?>
            <div class="alert alert-success">
                <i data-lucide="check-circle"></i>
                <div>
                    <strong>Booking Confirmed</strong><br>
                    Jadwal Anda sudah dikonfirmasi dan pembayaran telah diverifikasi.
                </div>
            </div>
            
            <a href="<?= BASE_URL ?>" class="btn btn--secondary mt-4">Kembali ke Home</a>

        <?php else: ?>
            
            <h2>Selesaikan Pembayaran (DP)</h2>
            <p>Silakan lakukan pembayaran DP untuk mengonfirmasi jadwal Anda.</p>
            
            <div class="timer" id="countdown">--:--:--</div>
            <p style="color: #aaa; font-size: 0.875rem;">Batas waktu pembayaran: <?= date('d M Y H:i', strtotime($booking['waktu_expired'])) ?></p>

            <div class="qris-dummy">
                <!-- QR Code -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=QRIS_<?= $bookingId ?>" alt="QRIS">
            </div>
            <p style="font-weight:bold;">Scan QRIS untuk Membayar</p>

            <div class="payment-details">
                <div class="payment-row">
                    <span>Layanan</span>
                    <span><?= e($booking['nama_layanan']) ?></span>
                </div>
                <div class="payment-row">
                    <span>Barber</span>
                    <span><?= e($booking['nama_barber']) ?></span>
                </div>
                <div class="payment-row">
                    <span>Tanggal & Jam</span>
                    <span><?= date('d M Y', strtotime($booking['tanggal'])) ?> - <?= date('H:i', strtotime($booking['jam'])) ?></span>
                </div>
                <div class="payment-row">
                    <span>Harga Total</span>
                    <span><?= formatRupiah($booking['total_harga']) ?></span>
                </div>
                <div class="payment-row total">
                    <span>Total DP (50%)</span>
                    <span><?= formatRupiah($booking['jumlah_dp']) ?></span>
                </div>
            </div>

            <form method="POST" action="">
                <button type="submit" name="simulate_payment" class="btn btn--primary btn--block">
                    Saya Sudah Membayar
                </button>
            </form>

        <?php endif; ?>
    </div>
</section>

<?php if ($booking['status'] === 'Pending Payment'): ?>
<script>
    const expiredTime = new Date("<?= date('Y-m-d\TH:i:s', strtotime($booking['waktu_expired'])) ?>").getTime();
    
    const x = setInterval(function() {
        const now = new Date().getTime();
        const distance = expiredTime - now;
        
        if (distance < 0) {
            clearInterval(x);
            document.getElementById("countdown").innerHTML = "EXPIRED";
            location.reload();
            return;
        }
        
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById("countdown").innerHTML = 
            (hours < 10 ? "0" + hours : hours) + ":" + 
            (minutes < 10 ? "0" + minutes : minutes) + ":" + 
            (seconds < 10 ? "0" + seconds : seconds);
    }, 1000);
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
