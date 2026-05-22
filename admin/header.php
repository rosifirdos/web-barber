<?php
/**
 * IF Barber — Admin Shared Header
 * Menyediakan layout sidebar dan proteksi autentikasi admin
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect the page - must be logged in as admin
requireAdmin();

$adminNama = $_SESSION['admin_nama'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminNama, 0, 1));

// Determine active page
if (!isset($activePage)) {
    $activePage = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'Admin Panel — ' . APP_NAME ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js (untuk Dashboard) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>

    <div class="admin-layout">
        <!-- ============================================
             SIDEBAR NAVIGATION
             ============================================ -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar__brand">
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="admin-sidebar__logo"><?= APP_NAME ?></a>
            </div>

            <nav class="admin-sidebar__menu">
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="admin-sidebar__link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="<?= BASE_URL ?>/admin/antrian.php" class="admin-sidebar__link <?= $activePage === 'antrian' ? 'active' : '' ?>">
                    <i data-lucide="calendar-clock"></i>
                    <span>Kelola Antrean</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/layanan.php" class="admin-sidebar__link <?= $activePage === 'layanan' ? 'active' : '' ?>">
                    <i data-lucide="scissors"></i>
                    <span>Kelola Layanan</span>
                </a>
            </nav>

            <div class="admin-sidebar__footer">
                <div class="admin-sidebar__user">
                    <div class="admin-sidebar__avatar">
                        <?= $adminInitial ?>
                    </div>
                    <div class="admin-sidebar__user-info">
                        <div class="admin-sidebar__user-name"><?= e($adminNama) ?></div>
                        <div class="admin-sidebar__user-role">Administrator</div>
                    </div>
                </div>

                <a href="javascript:void(0)" onclick="confirmAction('Apakah Anda yakin ingin keluar?', '<?= BASE_URL ?>/admin/logout.php')" class="admin-sidebar__link" style="color: #ef4444; background: rgba(239,68,68,0.02); margin-top: 10px;">
                    <i data-lucide="log-out"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- ============================================
             MAIN CONTENT
             ============================================ -->
        <div class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="admin-topbar__toggle" id="sidebarToggle">
                        <i data-lucide="menu"></i>
                    </button>
                    <h2 class="admin-topbar__title"><?= isset($headerTitle) ? $headerTitle : 'Dashboard' ?></h2>
                </div>

                <div class="admin-topbar__actions">
                    <a href="<?= BASE_URL ?>" target="_blank" class="admin-topbar__btn-visit">
                        <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
                        Lihat Website
                    </a>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="admin-content">
                <!-- Flash messages -->
                <?= getFlash() ?>
