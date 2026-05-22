<?php
/**
 * IF Barber — Admin Login
 * Halaman masuk untuk Administrator
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = sanitize($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        if (loginAdmin($conn, $username, $password)) {
            setFlash('success', 'Selamat datang kembali, ' . $_SESSION['admin_nama'] . '!');
            redirect(BASE_URL . '/admin/dashboard.php');
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — <?= APP_NAME ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at center, #14141f 0%, #0a0a0f 100%);
            padding: var(--space-lg);
            overflow-x: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            perspective: 1000px;
        }

        .login-card {
            background: rgba(18, 18, 29, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-2xl);
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transform: translateY(20px);
            opacity: 0;
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUpFade {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--space-xl);
        }

        .login-logo {
            font-family: 'Playfair Display', serif;
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--color-accent);
            letter-spacing: 1px;
            margin-bottom: var(--space-xs);
            display: inline-block;
        }

        .login-subtitle {
            font-size: var(--text-sm);
            color: var(--color-text-muted);
        }

        .login-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            font-size: var(--text-sm);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .login-error i {
            flex-shrink: 0;
        }

        .form-group {
            margin-bottom: var(--space-lg);
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: var(--space-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-text-muted);
            pointer-events: none;
            transition: color var(--transition-fast);
        }

        .input-icon-wrapper .form-input {
            padding-left: calc(var(--space-md) * 2 + 18px);
        }

        .input-icon-wrapper .form-input:focus ~ i {
            color: var(--color-accent);
        }

        .login-btn {
            margin-top: var(--space-lg);
        }

        .back-to-site {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
            font-size: var(--text-sm);
            color: var(--color-text-muted);
            margin-top: var(--space-xl);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .back-to-site:hover {
            color: var(--color-accent);
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <span class="login-logo"><?= APP_NAME ?> Admin</span>
                <p class="login-subtitle">Silakan login untuk mengelola barbershop</p>
            </div>

            <!-- Flash messages -->
            <?= getFlash() ?>

            <!-- Error message -->
            <?php if (!empty($error)): ?>
                <div class="login-error">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-icon-wrapper">
                        <input type="text" name="username" id="username" class="form-input" 
                               placeholder="Masukkan username" required autofocus
                               value="<?= isset($username) ? e($username) : '' ?>">
                        <i data-lucide="user" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-icon-wrapper">
                        <input type="password" name="password" id="password" class="form-input" 
                               placeholder="Masukkan password" required>
                        <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn--primary btn--block login-btn">
                    Masuk ke Dashboard
                    <i data-lucide="log-in" style="width: 16px; height: 16px;"></i>
                </button>
            </form>

            <a href="<?= BASE_URL ?>" class="back-to-site">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                Kembali ke Website Utama
            </a>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
