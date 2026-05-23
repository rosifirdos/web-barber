<?php
/**
 * IF Barber — Member Login
 */
$pageTitle = 'Login Member — IF Barber';
$activePage = 'member';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect jika sudah login
if (isMemberLoggedIn()) {
    redirect(BASE_URL . '/member/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, nama, no_hp, password FROM member WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $member = $result->fetch_assoc();
            if (password_verify($password, $member['password'])) {
                // Login sukses
                $_SESSION['member_id'] = $member['id'];
                $_SESSION['member_nama'] = $member['nama'];
                $_SESSION['member_hp'] = $member['no_hp'];
                redirect(BASE_URL . '/member/dashboard.php');
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Email tidak ditemukan.';
        }
        $stmt->close();
    }
}

require_once BASE_PATH . '/includes/header.php';
?>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: calc(var(--navbar-height) + var(--space-xl)) var(--space-md) var(--space-xl); background: linear-gradient(135deg, var(--color-bg-primary) 0%, var(--color-bg-card) 100%);">
    <div class="card" style="width: 100%; max-width: 400px;">
        <div style="text-align: center; margin-bottom: var(--space-lg);">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: rgba(200, 169, 110, 0.1); border-radius: 50%; color: var(--color-accent); margin-bottom: var(--space-md);">
                <i data-lucide="user" style="width: 30px; height: 30px;"></i>
            </div>
            <h1 style="font-family: var(--font-heading); color: var(--color-accent); margin-bottom: var(--space-xs);">Login Member</h1>
            <p style="color: var(--color-text-muted);">Masuk untuk mengelola booking Anda.</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; padding: 12px 15px; margin-bottom: 20px; border-radius: 4px; color: #ff8787; font-size: 14px;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <div class="form-input-wrapper">
                    <i data-lucide="mail" class="form-icon"></i>
                    <input type="email" id="email" name="email" class="form-input" placeholder="contoh@email.com" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: var(--space-xl);">
                <label class="form-label" for="password">Password</label>
                <div class="form-input-wrapper">
                    <i data-lucide="lock" class="form-icon"></i>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn--primary btn--full">Masuk</button>
        </form>

        <div style="text-align: center; margin-top: var(--space-lg); font-size: var(--text-sm); color: var(--color-text-muted);">
            Belum punya akun? <a href="<?= BASE_URL ?>/member/register.php" style="color: var(--color-accent); font-weight: 500;">Daftar di sini</a>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
