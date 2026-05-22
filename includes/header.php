<?php
/**
 * IF Barber — Header Template
 * Digunakan di semua halaman publik (bukan admin)
 *
 * Variables yang bisa di-set sebelum include:
 * - $pageTitle : judul halaman
 * - $pageDesc  : meta description
 * - $activePage: 'home', 'booking', 'member'
 */

if (!isset($pageTitle)) $pageTitle = APP_NAME . ' — ' . APP_TAGLINE;
if (!isset($pageDesc)) $pageDesc = 'IF Barber — Barbershop premium dengan layanan terbaik. Booking jadwal potong rambut online dengan mudah.';
if (!isset($activePage)) $activePage = 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDesc) ?>">
    <meta name="theme-color" content="#0a0a0f">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDesc) ?>">
    <meta property="og:type" content="website">

    <title><?= e($pageTitle) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>✂️</text></svg>">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/landing.css">

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

    <!-- Global JS Variables -->
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>

    <!-- Flash Message -->
    <?= getFlash() ?>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar__inner">
            <a href="<?= BASE_URL ?>" class="navbar__brand">
                <div>
                    <span class="navbar__logo">IF Barber</span>
                    <span class="navbar__logo-sub">Premium Grooming</span>
                </div>
            </a>

            <ul class="navbar__menu" id="navMenu">
                <li><a href="<?= BASE_URL ?>/#home" class="navbar__link <?= $activePage === 'home' ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= BASE_URL ?>/#about" class="navbar__link">About</a></li>
                <li><a href="<?= BASE_URL ?>/#services" class="navbar__link">Services</a></li>
                <li><a href="<?= BASE_URL ?>/#barbers" class="navbar__link">Barbers</a></li>
                <li><a href="<?= BASE_URL ?>/#gallery" class="navbar__link">Gallery</a></li>
                <li><a href="<?= BASE_URL ?>/#contact" class="navbar__link">Contact</a></li>
                
                <?php if (function_exists('isMemberLoggedIn') && isMemberLoggedIn()): ?>
                    <li><a href="<?= BASE_URL ?>/member/dashboard.php" class="navbar__link <?= $activePage === 'member' ? 'active' : '' ?>">Akun Saya</a></li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/member/login.php" class="navbar__link <?= $activePage === 'member' ? 'active' : '' ?>">Login</a></li>
                <?php endif; ?>

                <li>
                    <a href="<?= BASE_URL ?>/booking.php" class="btn btn--primary btn--sm <?= $activePage === 'booking' ? '' : '' ?>">
                        Book Now
                    </a>
                </li>
            </ul>

            <button class="navbar__toggle" id="navToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
