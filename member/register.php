<?php
/**
 * IF Barber — Member Register
 */
$pageTitle = 'Daftar Member — IF Barber';
$activePage = 'member';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect jika sudah login
if (isMemberLoggedIn()) {
    redirect(BASE_URL . '/member/dashboard.php');
}

$errors = [];
$nama = $email = $no_hp = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi input
    if (empty($nama)) $errors[] = 'Nama lengkap wajib diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (empty($no_hp)) $errors[] = 'Nomor HP wajib diisi.';
    if (empty($password) || strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $confirm_password) $errors[] = 'Konfirmasi password tidak cocok.';

    // Cek apakah email sudah terdaftar
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM member WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO member (nama, email, no_hp, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nama, $email, $no_hp, $hashedPassword);
        
        if ($stmt->execute()) {
            setFlash('success', 'Pendaftaran berhasil! Silakan login.');
            redirect(BASE_URL . '/member/login.php');
        } else {
            $errors[] = 'Terjadi kesalahan sistem. Coba lagi nanti.';
        }
        $stmt->close();
    }
}

require_once BASE_PATH . '/includes/header.php';
?>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: calc(var(--navbar-height) + var(--space-xl)) var(--space-md) var(--space-xl); background: linear-gradient(135deg, var(--color-bg-primary) 0%, var(--color-bg-card) 100%);">
    <div class="card" style="width: 100%; max-width: 450px;">
        <div class="card__body">
            <div style="text-align: center; margin-bottom: var(--space-lg);">
                <h1 style="font-family: var(--font-heading); color: var(--color-accent); margin-bottom: var(--space-xs);">Daftar Member</h1>
                <p style="color: var(--color-text-muted);">Gabung dengan IF Barber untuk kemudahan booking.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <ul style="margin: 0; padding-left: 20px; color: #ff8787; font-size: 14px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="nama" style="display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                        Nama Lengkap
                    </label>
                    <input type="text" id="nama" name="nama" class="form-input" value="<?= htmlspecialchars($nama) ?>" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email" style="display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="mail" style="width: 16px; height: 16px;"></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($email) ?>" placeholder="contoh@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="no_hp" style="display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="phone" style="width: 16px; height: 16px;"></i>
                        Nomor HP
                    </label>
                    <input type="text" id="no_hp" name="no_hp" class="form-input" value="<?= htmlspecialchars($no_hp) ?>" placeholder="08xxxxxx" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password" style="display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="lock" style="width: 16px; height: 16px;"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required>
                </div>

                <div class="form-group" style="margin-bottom: var(--space-xl);">
                    <label class="form-label" for="confirm_password" style="display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                        Konfirmasi Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Ulangi password" required>
                </div>

                <div style="text-align: center;">
                    <button type="submit" class="btn btn--primary" style="padding-left: 3rem; padding-right: 3rem;">Daftar Sekarang</button>
                </div>
            </form>

            <div style="text-align: center; margin-top: var(--space-lg); font-size: var(--text-sm); color: var(--color-text-muted);">
                Sudah punya akun? <a href="<?= BASE_URL ?>/member/login.php" style="color: var(--color-accent); font-weight: 500;">Login di sini</a>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
